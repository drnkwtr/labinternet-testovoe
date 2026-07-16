const BASE = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';

export class ApiError extends Error {
    constructor(status, payload) {
        super(`API ${status}`);
        this.status = status;
        this.payload = payload ?? {};
    }

    get validation() {
        return this.payload.errors ?? {};
    }

    get retryAfter() {
        return this.payload.retryAfter ?? null;
    }
}

export async function submitContact(form, { signal } = {}) {
    let response;

    try {
        response = await fetch(`${BASE}/contact`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(form),
            signal,
        });
    } catch (cause) {
        throw new ApiError(0, { message: 'Не удалось связаться с сервером. Проверьте, что API запущен.' });
    }

    if (response.status === 204) {
        return true;
    }

    let payload = {};
    try {
        payload = await response.json();
    } catch {
        // сервер вернул не-JSON — оставляем пустой payload
    }

    if (response.status === 429) {
        payload.retryAfter = Number(response.headers.get('Retry-After')) || null;
    }

    throw new ApiError(response.status, payload);
}
