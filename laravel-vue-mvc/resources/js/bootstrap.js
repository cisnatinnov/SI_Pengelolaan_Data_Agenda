import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Redirect to the login page when the session is missing or expired.
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if ([401, 419].includes(error.response?.status)) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
