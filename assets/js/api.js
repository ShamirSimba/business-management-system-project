// API JavaScript file for BMS

const BmsApi = {
    baseUrl: '/BMS/api/v1/',

    getToken() {
        return localStorage.getItem('bms_api_token');
    },

    setToken(token) {
        localStorage.setItem('bms_api_token', token);
    },

    async request(endpoint, method = 'GET', data = null) {
        const url = this.baseUrl + endpoint;
        const headers = {
            'Content-Type': 'application/json'
        };
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        const response = await fetch(url, {
            method,
            headers,
            body: data ? JSON.stringify(data) : null
        });

        if (response.status === 401) {
            localStorage.removeItem('bms_api_token');
            window.location.href = '/BMS/auth/login.php';
            return;
        }

        const payload = await response.json();
        if (!response.ok) {
            const error = payload.message || 'An unexpected error occurred';
            throw new Error(error);
        }

        return payload;
    },

    get(endpoint) {
        return this.request(endpoint, 'GET');
    },

    post(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    },

    put(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    },

    delete(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
};
