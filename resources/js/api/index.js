import axios from 'axios';

export function validateCode(code) {
    return axios.post('/api/access-code/validate', { code });
}

export function getStatus() {
    return axios.get('/api/access-code/status');
}

export function logout() {
    return axios.post('/api/access-code/logout');
}
