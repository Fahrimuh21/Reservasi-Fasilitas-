import axios from 'axios';
import { ref } from 'vue';

const AUTH_TOKEN_KEY = 'reservasi_auth_token';
const AUTH_USER_KEY = 'reservasi_auth_user';

export const authUser = ref(null);

export function getToken() {
    return localStorage.getItem(AUTH_TOKEN_KEY);
}

export function getAuthUser() {
    const raw = localStorage.getItem(AUTH_USER_KEY);
    if (!raw) return null;

    try {
        return JSON.parse(raw);
    } catch (error) {
        console.error('Failed to parse auth user from localStorage', error);
        localStorage.removeItem(AUTH_USER_KEY);
        return null;
    }
}

export function isAuthenticated() {
    return Boolean(getToken());
}

export function applyAuthHeader(token = getToken()) {
    if (!token) {
        delete axios.defaults.headers.common.Authorization;
        return;
    }

    axios.defaults.headers.common.Authorization = `Bearer ${token}`;
}

export function setAuthSession(token, user) {
    localStorage.setItem(AUTH_TOKEN_KEY, token);
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user));
    authUser.value = user;
    applyAuthHeader(token);
}

export function clearAuthSession() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(AUTH_USER_KEY);
    authUser.value = null;
    delete axios.defaults.headers.common.Authorization;
}

/**
 * Restore the cached token, then verify it against the server.
 * A network failure keeps the cached session so a temporary outage does not
 * immediately log the user out. A rejected token is always removed.
 */
export async function restoreAuthSession() {
    const token = getToken();
    authUser.value = getAuthUser();
    if (!token) {
        if (authUser.value) clearAuthSession();
        return null;
    }

    applyAuthHeader(token);

    try {
        const response = await axios.get('/api/me', { timeout: 10000 });
        localStorage.setItem(AUTH_USER_KEY, JSON.stringify(response.data));
        authUser.value = response.data;
        return response.data;
    } catch (error) {
        if (error.response?.status === 401) {
            clearAuthSession();
            return null;
        }

        return authUser.value;
    }
}

let interceptorId = null;

/** Clear a stale local login whenever a protected API rejects its token. */
export function installAuthInterceptor() {
    if (interceptorId !== null) return;

    interceptorId = axios.interceptors.response.use(
        (response) => response,
        (error) => {
            const isLoginRequest = error.config?.url?.endsWith('/api/login');

            if (error.response?.status === 401 && getToken() && !isLoginRequest) {
                clearAuthSession();
            }

            return Promise.reject(error);
        },
    );
}

export function hasRole(...roles) {
    const user = getAuthUser();

    if (!user) {
        return false;
    }

    return roles.includes(user.role);
}
