<script setup>
import { reactive, ref, computed, onBeforeUnmount } from 'vue';
import { submitContact, ApiError } from '../api.js';

const STAGES = [
    { key: 'request', code: 'REQ', label: 'Запрос принят' },
    { key: 'validation', code: '422?', label: 'Валидация полей' },
    { key: 'logic', code: 'JOB', label: 'Сохранение и очередь' },
    { key: 'ai', code: 'AI', label: 'AI-цитата по комментарию' },
    { key: 'mail', code: 'SMTP', label: 'Письмо владельцу и копия вам' },
    { key: 'response', code: '204', label: 'Ответ отправлен' },
];

const reducedMotion =
    typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

const form = reactive({ name: '', phone: '', email: '', comment: '' });
const serverErrors = reactive({});
const touched = reactive({});

const phase = ref('idle'); // idle | sending | done | error
const activeStage = ref(-1);
const failedStage = ref(-1);
const generalError = ref('');
const retryIn = ref(0);

let walkTimer = null;
let retryTimer = null;
let controller = null;

const rules = {
    name: (v) => (v.trim().length < 2 ? 'Минимум 2 символа.' : ''),
    phone: (v) =>
        v.trim().length < 5
            ? 'Минимум 5 символов.'
            : !/^\+?[0-9\s\-()]+$/.test(v.trim())
              ? 'Только цифры, пробелы и + - ( ).'
              : '',
    email: (v) => (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? 'Похоже на некорректный email.' : ''),
    comment: (v) => (v.trim().length < 5 ? 'Расскажите чуть подробнее — минимум 5 символов.' : ''),
};

const clientError = (field) => (touched[field] ? rules[field](form[field]) : '');
const errorFor = (field) => serverErrors[field]?.[0] ?? clientError(field);

const isValid = computed(() => Object.keys(rules).every((f) => !rules[f](form[f])));
const busy = computed(() => phase.value === 'sending');

function markTouched(field) {
    touched[field] = true;
    delete serverErrors[field];
}

function clearWalk() {
    if (walkTimer) clearInterval(walkTimer);
    walkTimer = null;
}

function startWalk() {
    activeStage.value = 0;
    if (reducedMotion) return;
    walkTimer = setInterval(() => {
        // ползём по конвейеру, но не «перепрыгиваем» финальную стадию до ответа
        if (activeStage.value < STAGES.length - 2) activeStage.value += 1;
    }, 420);
}

async function onSubmit() {
    Object.keys(rules).forEach((f) => (touched[f] = true));
    Object.keys(serverErrors).forEach((f) => delete serverErrors[f]);
    generalError.value = '';
    failedStage.value = -1;

    if (!isValid.value) {
        phase.value = 'error';
        failedStage.value = 1;
        return;
    }

    phase.value = 'sending';
    startWalk();
    controller = new AbortController();

    try {
        await submitContact({ ...form }, { signal: controller.signal });
        clearWalk();
        activeStage.value = STAGES.length - 1;
        phase.value = 'done';
    } catch (err) {
        clearWalk();
        handleFailure(err);
    }
}

function handleFailure(err) {
    phase.value = 'error';

    if (!(err instanceof ApiError)) {
        failedStage.value = 2;
        generalError.value = 'Неизвестная ошибка. Попробуйте ещё раз.';
        return;
    }

    if (err.status === 422) {
        failedStage.value = 1;
        Object.assign(serverErrors, err.validation);
        generalError.value = 'Проверьте выделенные поля.';
        return;
    }

    if (err.status === 429) {
        failedStage.value = 0;
        startRetry(err.retryAfter ?? 60);
        return;
    }

    if (err.status === 0) {
        failedStage.value = 0;
        generalError.value = err.payload.message;
        return;
    }

    failedStage.value = 2;
    generalError.value = err.payload.message ?? `Сервер вернул ${err.status}. Попробуйте позже.`;
}

function startRetry(seconds) {
    retryIn.value = seconds;
    generalError.value = 'Слишком много обращений. Антиспам временно придержал форму.';
    retryTimer = setInterval(() => {
        retryIn.value -= 1;
        if (retryIn.value <= 0) {
            clearInterval(retryTimer);
            retryTimer = null;
            phase.value = 'idle';
        }
    }, 1000);
}

function reset() {
    form.name = form.phone = form.email = form.comment = '';
    Object.keys(touched).forEach((f) => delete touched[f]);
    Object.keys(serverErrors).forEach((f) => delete serverErrors[f]);
    phase.value = 'idle';
    activeStage.value = -1;
    failedStage.value = -1;
    generalError.value = '';
}

const stageState = (i) => {
    if (phase.value === 'error' && i === failedStage.value) return 'fail';
    if (i <= activeStage.value) return 'done';
    if (i === activeStage.value + 1 && phase.value === 'sending') return 'active';
    return 'idle';
};

onBeforeUnmount(() => {
    clearWalk();
    if (retryTimer) clearInterval(retryTimer);
    controller?.abort();
});
</script>

<template>
    <div class="wrap">
        <!-- УСПЕХ -->
        <div v-if="phase === 'done'" class="done">
            <div class="done-code mono">204<span>No Content</span></div>
            <h3>Обращение принято в обработку.</h3>
            <p>
                Оно сохранено, поставлено в очередь и уже проходит конвейер. На указанную почту придёт копия —
                вместе с короткой тематической цитатой, которую бэкенд сгенерировал по вашему комментарию через AI.
            </p>
            <div class="pipeline" aria-hidden="true">
                <span v-for="s in STAGES" :key="s.key" class="dot done"></span>
            </div>
            <button type="button" class="ghost" @click="reset">Отправить ещё одно</button>
        </div>

        <!-- ФОРМА -->
        <form v-else class="form" novalidate @submit.prevent="onSubmit">
            <div class="field" :class="{ bad: errorFor('name') }">
                <label for="f-name">Имя</label>
                <input
                    id="f-name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    placeholder="Как к вам обращаться"
                    :disabled="busy"
                    @blur="markTouched('name')"
                />
                <span v-if="errorFor('name')" class="msg">{{ errorFor('name') }}</span>
            </div>

            <div class="row">
                <div class="field" :class="{ bad: errorFor('phone') }">
                    <label for="f-phone">Телефон</label>
                    <input
                        id="f-phone"
                        v-model="form.phone"
                        type="tel"
                        autocomplete="tel"
                        placeholder="+7 900 123-45-67"
                        :disabled="busy"
                        @blur="markTouched('phone')"
                    />
                    <span v-if="errorFor('phone')" class="msg">{{ errorFor('phone') }}</span>
                </div>

                <div class="field" :class="{ bad: errorFor('email') }">
                    <label for="f-email">Email</label>
                    <input
                        id="f-email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="you@example.com"
                        :disabled="busy"
                        @blur="markTouched('email')"
                    />
                    <span v-if="errorFor('email')" class="msg">{{ errorFor('email') }}</span>
                </div>
            </div>

            <div class="field" :class="{ bad: errorFor('comment') }">
                <label for="f-comment">Комментарий</label>
                <textarea
                    id="f-comment"
                    v-model="form.comment"
                    rows="4"
                    placeholder="О каком проекте или задаче речь?"
                    :disabled="busy"
                    @blur="markTouched('comment')"
                ></textarea>
                <span v-if="errorFor('comment')" class="msg">{{ errorFor('comment') }}</span>
            </div>

            <!-- Живой конвейер запроса -->
            <div v-if="phase === 'sending' || phase === 'error'" class="stepper" aria-live="polite">
                <div v-for="(s, i) in STAGES" :key="s.key" class="step" :data-state="stageState(i)">
                    <span class="step-code mono">{{ s.code }}</span>
                    <span class="step-label">{{ s.label }}</span>
                </div>
            </div>

            <p v-if="generalError" class="alert mono">
                <span v-if="retryIn > 0">повторить через {{ retryIn }}с · </span>{{ generalError }}
            </p>

            <button type="submit" class="submit" :disabled="busy || retryIn > 0">
                <span v-if="busy" class="mono">отправка…</span>
                <span v-else-if="retryIn > 0" class="mono">пауза {{ retryIn }}с</span>
                <span v-else>Отправить обращение</span>
            </button>

            <p class="fineprint mono">POST /api/v1/contact · защищено rate-limit · валидация на сервере</p>
        </form>
    </div>
</template>

<style scoped>
.wrap {
    width: 100%;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.field label {
    font-family: var(--font-mono);
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted);
}

.field input,
.field textarea {
    font-family: var(--font-body);
    font-size: 16px;
    color: var(--ink);
    background: var(--paper);
    border: 1.5px solid var(--line-strong);
    border-radius: var(--radius);
    padding: 12px 14px;
    width: 100%;
    transition:
        border-color 0.15s ease,
        background 0.15s ease;
}

.field textarea {
    resize: vertical;
    min-height: 104px;
}

.field input:hover,
.field textarea:hover {
    border-color: var(--ink-soft);
}

.field input:focus,
.field textarea:focus {
    outline: none;
    border-color: var(--ink);
    background: #fff;
}

.field.bad input,
.field.bad textarea {
    border-color: var(--accent);
    background: #fdf0ed;
}

.field .msg {
    font-size: 13px;
    color: var(--accent-ink);
}

.stepper {
    display: flex;
    flex-direction: column;
    gap: 2px;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 10px 12px;
    background: var(--paper-2);
}

.step {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 5px 0;
    color: var(--muted);
    transition:
        color 0.25s ease,
        opacity 0.25s ease;
}

.step-code {
    font-size: 11px;
    letter-spacing: 0.04em;
    width: 46px;
    flex-shrink: 0;
    padding: 2px 0;
    text-align: center;
    border: 1px solid var(--line-strong);
    border-radius: 3px;
    color: var(--muted);
    background: var(--paper);
}

.step-label {
    font-size: 14px;
}

.step[data-state='active'] {
    color: var(--ink);
}
.step[data-state='active'] .step-code {
    border-color: var(--ink);
    color: var(--ink);
    animation: pulse 1s ease-in-out infinite;
}

.step[data-state='done'] {
    color: var(--ink);
}
.step[data-state='done'] .step-code {
    background: var(--ink);
    color: var(--paper);
    border-color: var(--ink);
}

.step[data-state='fail'] {
    color: var(--accent-ink);
}
.step[data-state='fail'] .step-code {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.45;
    }
}

.alert {
    margin: 0;
    font-size: 13px;
    color: var(--accent-ink);
    background: #fdf0ed;
    border-left: 3px solid var(--accent);
    padding: 10px 12px;
    border-radius: 0 var(--radius) var(--radius) 0;
}

.submit {
    font-family: var(--font-display);
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.01em;
    color: var(--paper);
    background: var(--ink);
    border: none;
    border-radius: var(--radius);
    padding: 15px 20px;
    cursor: pointer;
    transition:
        transform 0.12s ease,
        background 0.15s ease;
}

.submit:hover:not(:disabled) {
    background: var(--accent);
}

.submit:active:not(:disabled) {
    transform: translateY(1px);
}

.submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.fineprint {
    margin: 0;
    font-size: 12px;
    color: var(--muted);
    text-align: center;
}

.done {
    text-align: center;
    padding: 8px 4px;
    animation: rise 0.4s ease both;
}

.done-code {
    display: inline-flex;
    align-items: baseline;
    gap: 10px;
    font-size: 52px;
    font-weight: 600;
    color: var(--ok);
    line-height: 1;
}

.done-code span {
    font-size: 14px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
}

.done h3 {
    font-family: var(--font-display);
    font-size: 22px;
    margin: 18px 0 8px;
}

.done p {
    color: var(--ink-soft);
    max-width: 44ch;
    margin: 0 auto 20px;
    font-size: 15px;
}

.pipeline {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 22px;
}

.pipeline .dot {
    width: 26px;
    height: 5px;
    border-radius: 3px;
    background: var(--ok);
    opacity: 0.85;
}

.ghost {
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--ink);
    background: transparent;
    border: 1.5px solid var(--line-strong);
    border-radius: var(--radius);
    padding: 10px 18px;
    cursor: pointer;
    transition: border-color 0.15s ease;
}

.ghost:hover {
    border-color: var(--ink);
}

@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@media (max-width: 560px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>
