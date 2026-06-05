import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '10s', target: 10 },
        { duration: '20s', target: 300 },
        { duration: '1m', target: 1000 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% des requêtes doivent être < 500ms
    },
};

export default function () {
    // On va sur la page login
    const res = http.get('http://nginx:8080'); 
    check(res, { 'status est 200': (r) => r.status === 200 });
    sleep(1);
    // On se connecte en POST
    const payload = JSON.stringify(
        { 
            _username: 'admin',
            _password: 'admin' 
        }
    );
    const params = {
        headers: {
        'Content-Type': 'application/json',
        },
    };
    const post = http.post('http://nginx:8080/agent/security/login', payload, params);
    check(post, { 'status est 200': (r) => r.status === 200 });
    sleep(1);
}