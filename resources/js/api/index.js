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

export function getProducts(params = {}) {
    return axios.get('/api/products', { params });
}

export function getProduct(id) {
    return axios.get(`/api/products/${id}`);
}

export function getFilters() {
    return axios.get('/api/filters');
}
