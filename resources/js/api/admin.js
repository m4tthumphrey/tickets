import axios from 'axios';

export function adminLogin(email, password) {
    return axios.post('/api/admin/login', { email, password });
}

export function adminLogout() {
    return axios.post('/api/admin/logout');
}

export function adminStatus() {
    return axios.get('/api/admin/status');
}

export function getAccessCodes(params = {}) {
    return axios.get('/api/admin/access-codes', { params });
}

export function getAccessCode(id) {
    return axios.get(`/api/admin/access-codes/${id}`);
}

export function createAccessCode(data) {
    return axios.post('/api/admin/access-codes', data);
}

export function updateAccessCode(id, data) {
    return axios.put(`/api/admin/access-codes/${id}`, data);
}

export function revokeAccessCode(id) {
    return axios.post(`/api/admin/access-codes/${id}/revoke`);
}

export function deleteAccessCode(id) {
    return axios.delete(`/api/admin/access-codes/${id}`);
}

export function getAdminFilters() {
    return axios.get('/api/admin/filters');
}
