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

export function restoreAuthSession() {
    const token = getToken();
    authUser.value = getAuthUser();
    if (token) {
        applyAuthHeader(token);
    }
}

export function hasRole(...roles){

    const user = getAuthUser();

    if(!user){
        return false;
    }

    return roles.includes(user.role);

}