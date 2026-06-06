import http from "k6/http";
import { check, group, sleep } from "k6";

// =====================================
// CONFIG
// =====================================
const BASE_URL = "http://127.0.0.1:8000";

const USER_EMAIL = "user@example.com";
const USER_PASSWORD = "passpass";

const ADMIN_EMAIL = "admin@example.com";
const ADMIN_PASSWORD = "passpass";

const EBOOK_SLUG = "voluptates-delectus-7118";

// =====================================
// LOAD TEST SETTINGS
// =====================================
export const options = {
    stages: [
        { duration: "1m", target: 10 },
        { duration: "2m", target: 20 },
        { duration: "2m", target: 50 },
        { duration: "1m", target: 100 },
        { duration: "1m", target: 200 },
        { duration: "1m", target: 300 },
        { duration: "1m", target: 0 },
    ],

    thresholds: {
        http_req_duration: ["p(95)<2000"],
        http_req_failed: ["rate<0.05"],
    },
};

const JSON_HEADERS = {
    "Content-Type": "application/json",
};

// =====================================
// HELPERS
// =====================================
function login(email, password) {
    const response = http.post(
        `${BASE_URL}/api/v1/auth/login`,
        JSON.stringify({
            email,
            password,
        }),
        {
            headers: JSON_HEADERS,
        },
    );

    check(response, {
        "login success": (r) => r.status === 200,
    });

    if (response.status !== 200) {
        console.error(`LOGIN FAILED: ${email} => ${response.status}`);
        return null;
    }

    try {
        return response.json("token") || response.json("data.token");
    } catch {
        return null;
    }
}

function authHeaders(token) {
    return {
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        },
    };
}

// =====================================
// SETUP (RUN ONCE)
// =====================================
export function setup() {
    const userToken = login(USER_EMAIL, USER_PASSWORD);

    const adminToken = login(ADMIN_EMAIL, ADMIN_PASSWORD);

    if (!userToken) {
        throw new Error("User login failed");
    }

    if (!adminToken) {
        throw new Error("Admin login failed");
    }

    return {
        userToken,
        adminToken,
    };
}

// =====================================
// MAIN TEST
// =====================================
export default function (data) {
    const { userToken, adminToken } = data;

    group("Public APIs", () => {
        const ebooksResponse = http.get(`${BASE_URL}/api/v1/ebooks`);

        check(ebooksResponse, {
            "ebooks list loaded": (r) => r.status === 200,
        });

        const ebookResponse = http.get(
            `${BASE_URL}/api/v1/ebooks/${EBOOK_SLUG}`,
        );

        check(ebookResponse, {
            "ebook details loaded": (r) => r.status === 200,
        });

        sleep(1);
    });

    group("User APIs", () => {
        const meResponse = http.get(
            `${BASE_URL}/api/v1/auth/me`,
            authHeaders(userToken),
        );

        check(meResponse, {
            "me endpoint works": (r) => r.status === 200,
        });

        const purchasesResponse = http.get(
            `${BASE_URL}/api/v1/dashboard/purchases`,
            authHeaders(userToken),
        );

        check(purchasesResponse, {
            "purchases loaded": (r) => r.status === 200,
        });

        sleep(1);
    });

    group("Admin APIs", () => {
        const ebooksResponse = http.get(
            `${BASE_URL}/api/v1/admin/ebooks`,
            authHeaders(adminToken),
        );

        check(ebooksResponse, {
            "admin ebooks loaded": (r) => r.status === 200,
        });

        const ordersResponse = http.get(
            `${BASE_URL}/api/v1/admin/orders`,
            authHeaders(adminToken),
        );

        check(ordersResponse, {
            "admin orders loaded": (r) => r.status === 200,
        });

        sleep(1);
    });

    sleep(1);
}
