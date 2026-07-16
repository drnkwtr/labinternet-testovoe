<script setup>
import ContactForm from './components/ContactForm.vue';

const lifecycle = [
    { n: '01', code: 'REQUEST', title: 'Запрос', text: 'Форма уходит на POST /api/v1/contact. Каждый запрос пишется в отдельный лог-файл.' },
    { n: '02', code: 'VALIDATE', title: 'Валидация', text: 'Имя, телефон, email и комментарий проходят строгие правила и санитизацию. Иначе — 422 с разбором по полям.' },
    { n: '03', code: 'DISPATCH', title: 'Бизнес-логика', text: 'Обращение сохраняется в БД через репозиторий и уходит в очередь отдельной Job.' },
    { n: '04', code: 'AI', title: 'AI', text: 'По комментарию генерируется тематическая цитата. Нет ключа или лимита — включается эвристический fallback.' },
    { n: '05', code: 'DELIVER', title: 'Отправка', text: 'Письмо владельцу и копия отправителю — с той самой цитатой внутри.' },
    { n: '06', code: 'RESPONSE', title: 'Ответ', text: '204 No Content: принято в обработку. Быстрый ответ клиенту, тяжёлое — в очереди.' },
];

const capabilities = [
    {
        code: 'API',
        title: 'Проектирование API',
        text: 'Версионирование, честные статус-коды, предсказуемые контракты. Документация в OpenAPI/Swagger, а не в голове.',
    },
    {
        code: 'ARCH',
        title: 'Слоистая архитектура',
        text: 'Controllers → Services → Repositories. Request → DTO → Job. Границы, которые держат проект по мере роста.',
    },
    {
        code: 'AI',
        title: 'AI в проде',
        text: 'Интеграция LLM с обязательным graceful fallback — сервис не падает, когда падает провайдер.',
    },
    {
        code: 'OPS',
        title: 'Надёжность',
        text: 'Rate-limit против спама, логирование запросов, глобальный error handler, health- и metrics-эндпоинты.',
    },
];

const codes = [
    { c: '204', d: 'принято' },
    { c: '422', d: 'валидация' },
    { c: '429', d: 'анти-спам' },
    { c: '503', d: 'health' },
];
</script>

<template>
    <div class="page">
        <header class="topbar">
            <div class="shell topbar-inner">
                <a class="brand mono" href="#top">@drnkwtr<span class="cursor">_</span></a>
                <nav class="nav mono">
                    <a href="#flow">конвейер</a>
                    <a href="#stack">стек</a>
                    <a href="#contact">связаться</a>
                </nav>
            </div>
        </header>

        <!-- HERO -->
        <section id="top" class="hero">
            <div class="shell">
                <p class="eyebrow mono">GET / — backend-разработчик · открыт к проектам</p>
                <h1 class="headline">
                    Собираю бэкенд, где каждый запрос
                    <span class="mark">проходит предсказуемый путь</span>
                    от валидации до ответа.
                </h1>
                <p class="lede">
                    PHP · Laravel · слоистая архитектура, очереди, AI-интеграции и честная обработка ошибок.
                    Эта страница — не витрина, а работающий пример: форма ниже проходит ровно тот же конвейер,
                    что я строю в проде.
                </p>
                <div class="hero-cta">
                    <a href="#contact" class="btn-primary">Написать мне</a>
                    <a href="#flow" class="btn-link mono">↓ как это работает</a>
                </div>

                <div class="codes" aria-label="Статус-коды, на которых говорит API">
                    <div v-for="c in codes" :key="c.c" class="code-chip">
                        <span class="mono code-num">{{ c.c }}</span>
                        <span class="code-desc">{{ c.d }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- LIFECYCLE -->
        <section id="flow" class="flow">
            <div class="shell">
                <div class="section-head">
                    <span class="tag mono">// request lifecycle</span>
                    <h2>Путь одного обращения</h2>
                    <p class="section-sub">
                        Полный цикл, который проходит форма: запрос → валидация → бизнес-логика → AI → отправка →
                        ответ. Реальная последовательность — поэтому и пронумерована.
                    </p>
                </div>
                <ol class="lifecycle">
                    <li v-for="step in lifecycle" :key="step.n" class="stage">
                        <div class="stage-top">
                            <span class="stage-n mono">{{ step.n }}</span>
                            <span class="stage-code mono">{{ step.code }}</span>
                        </div>
                        <h3>{{ step.title }}</h3>
                        <p>{{ step.text }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- CAPABILITIES -->
        <section id="stack" class="stack">
            <div class="shell">
                <div class="section-head">
                    <span class="tag mono">// what I bring</span>
                    <h2>За что отвечаю в проекте</h2>
                </div>
                <div class="cards">
                    <article v-for="cap in capabilities" :key="cap.code" class="card">
                        <span class="card-code mono">{{ cap.code }}</span>
                        <h3>{{ cap.title }}</h3>
                        <p>{{ cap.text }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- CONTACT -->
        <section id="contact" class="contact">
            <div class="shell contact-grid">
                <div class="contact-intro">
                    <span class="tag mono">// POST /api/v1/contact</span>
                    <h2>Расскажите о задаче</h2>
                    <p>
                        Отправьте обращение — оно пройдёт весь конвейер слева направо в реальном времени. На почту
                        придёт копия с AI-цитатой по вашему комментарию.
                    </p>
                    <ul class="assurances mono">
                        <li><span>✓</span> ответ 204 — заявка в очереди за миллисекунды</li>
                        <li><span>✓</span> ошибки честные: 422 по полям, 429 при спаме</li>
                        <li><span>✓</span> ничего не теряется — всё логируется и хранится</li>
                    </ul>
                </div>
                <div class="contact-card">
                    <ContactForm />
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="shell footer-inner mono">
                <span>@drnkwtr · backend · {{ new Date().getFullYear() }}</span>
                <span class="footer-links">
                    <a href="/api/documentation">Swagger</a>
                    <a href="/api/v1/health">/health</a>
                    <a href="/api/v1/metrics">/metrics</a>
                </span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.shell {
    max-width: var(--maxw);
    margin: 0 auto;
    padding: 0 28px;
}

/* topbar */
.topbar {
    position: sticky;
    top: 0;
    z-index: 20;
    backdrop-filter: blur(8px);
    background: color-mix(in srgb, var(--paper) 82%, transparent);
    border-bottom: 1px solid var(--line);
}

.topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
}

.brand {
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.02em;
}

.cursor {
    color: var(--accent);
    animation: blink 1.1s step-end infinite;
}

@keyframes blink {
    50% {
        opacity: 0;
    }
}

.nav {
    display: flex;
    gap: 26px;
    font-size: 13px;
}

.nav a {
    color: var(--muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.nav a:hover {
    color: var(--ink);
}

/* hero */
.hero {
    padding: 88px 0 72px;
    border-bottom: 1px solid var(--line);
}

.eyebrow {
    font-size: 13px;
    color: var(--accent-ink);
    letter-spacing: 0.04em;
    margin: 0 0 26px;
}

.headline {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: clamp(2.1rem, 5.4vw, 3.9rem);
    line-height: 1.05;
    letter-spacing: -0.02em;
    max-width: 15ch;
    margin: 0 0 26px;
}

.mark {
    color: var(--accent);
}

.lede {
    max-width: 60ch;
    font-size: clamp(1rem, 2vw, 1.18rem);
    color: var(--ink-soft);
    margin: 0 0 34px;
}

.hero-cta {
    display: flex;
    align-items: center;
    gap: 22px;
    flex-wrap: wrap;
}

.btn-primary {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    color: var(--paper);
    background: var(--ink);
    padding: 14px 26px;
    border-radius: var(--radius);
    transition:
        background 0.15s ease,
        transform 0.12s ease;
}

.btn-primary:hover {
    background: var(--accent);
}

.btn-primary:active {
    transform: translateY(1px);
}

.btn-link {
    font-size: 13px;
    color: var(--muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.btn-link:hover {
    color: var(--ink);
}

.codes {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 52px;
}

.code-chip {
    display: flex;
    align-items: baseline;
    gap: 9px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius);
    padding: 9px 15px;
    background: var(--paper-2);
}

.code-num {
    font-size: 19px;
    font-weight: 600;
    color: var(--ink);
}

.code-desc {
    font-size: 12px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* section heads */
.section-head {
    margin-bottom: 44px;
}

.tag {
    display: inline-block;
    font-size: 12px;
    color: var(--accent-ink);
    letter-spacing: 0.05em;
    margin-bottom: 14px;
}

.section-head h2,
.contact-intro h2 {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: clamp(1.7rem, 3.6vw, 2.5rem);
    letter-spacing: -0.015em;
    margin: 0;
}

.section-sub {
    max-width: 62ch;
    color: var(--ink-soft);
    margin: 14px 0 0;
}

/* lifecycle */
.flow {
    padding: 76px 0;
    border-bottom: 1px solid var(--line);
}

.lifecycle {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--line);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    overflow: hidden;
}

.stage {
    background: var(--paper);
    padding: 26px 24px 30px;
    transition: background 0.2s ease;
}

.stage:hover {
    background: var(--paper-2);
}

.stage-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.stage-n {
    font-size: 34px;
    font-weight: 600;
    color: var(--accent);
    line-height: 1;
}

.stage-code {
    font-size: 11px;
    letter-spacing: 0.1em;
    color: var(--muted);
    border: 1px solid var(--line-strong);
    border-radius: 3px;
    padding: 3px 8px;
}

.stage h3 {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 19px;
    margin: 0 0 8px;
}

.stage p {
    font-size: 14.5px;
    color: var(--ink-soft);
    margin: 0;
}

/* capabilities */
.stack {
    padding: 76px 0;
    border-bottom: 1px solid var(--line);
}

.cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.card {
    border: 1px solid var(--line-strong);
    border-radius: var(--radius);
    padding: 28px 26px;
    background: var(--paper);
    position: relative;
    transition:
        border-color 0.18s ease,
        transform 0.18s ease;
}

.card:hover {
    border-color: var(--ink);
    transform: translateY(-2px);
}

.card-code {
    font-size: 12px;
    letter-spacing: 0.1em;
    color: var(--paper);
    background: var(--ink);
    padding: 3px 9px;
    border-radius: 3px;
}

.card h3 {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 20px;
    margin: 18px 0 10px;
}

.card p {
    font-size: 15px;
    color: var(--ink-soft);
    margin: 0;
}

/* contact */
.contact {
    padding: 84px 0;
}

.contact-grid {
    display: grid;
    grid-template-columns: 0.85fr 1.15fr;
    gap: 56px;
    align-items: start;
}

.contact-intro {
    position: sticky;
    top: 90px;
}

.contact-intro h2 {
    margin-top: 14px;
}

.contact-intro > p {
    color: var(--ink-soft);
    margin: 16px 0 26px;
}

.assurances {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.assurances li {
    font-size: 13px;
    color: var(--ink-soft);
    display: flex;
    gap: 10px;
}

.assurances span {
    color: var(--ok);
    font-weight: 600;
}

.contact-card {
    background: #fff;
    border: 1px solid var(--line-strong);
    border-radius: 8px;
    padding: 34px;
    box-shadow: 0 1px 0 var(--line-strong), 0 18px 40px -28px rgba(23, 25, 28, 0.4);
}

/* footer */
.footer {
    border-top: 1px solid var(--line);
    padding: 24px 0;
}

.footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--muted);
    flex-wrap: wrap;
    gap: 12px;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.footer-links a {
    color: var(--muted);
    text-decoration: none;
    transition: color 0.15s ease;
}

.footer-links a:hover {
    color: var(--accent-ink);
}

/* responsive */
@media (max-width: 900px) {
    .lifecycle {
        grid-template-columns: repeat(2, 1fr);
    }
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 36px;
    }
    .contact-intro {
        position: static;
    }
}

@media (max-width: 640px) {
    .shell {
        padding: 0 20px;
    }
    .hero {
        padding: 60px 0 52px;
    }
    .nav {
        display: none;
    }
    .lifecycle {
        grid-template-columns: 1fr;
    }
    .cards {
        grid-template-columns: 1fr;
    }
    .flow,
    .stack {
        padding: 56px 0;
    }
}
</style>
