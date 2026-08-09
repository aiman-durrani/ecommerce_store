/**
 * Shared Store API client & Auth/Cart Badge state manager
 */

const API_BASE = '/api';

function getToken() {
  return localStorage.getItem('auth_token');
}

function setToken(token) {
  if (token) {
    localStorage.setItem('auth_token', token);
  } else {
    localStorage.removeItem('auth_token');
  }
  updateAuthUI();
}

function getUser() {
  const user = localStorage.getItem('auth_user');
  return user ? JSON.parse(user) : null;
}

function setUser(user) {
  if (user) {
    localStorage.setItem('auth_user', JSON.stringify(user));
  } else {
    localStorage.removeItem('auth_user');
  }
  updateAuthUI();
}

async function apiFetch(endpoint, options = {}) {
  const token = getToken();
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE}${endpoint}`, {
    ...options,
    headers,
  });

  if (response.status === 401) {
    setToken(null);
    setUser(null);
    window.dispatchEvent(new CustomEvent('auth-changed'));
  }

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || 'An error occurred');
    error.status = response.status;
    error.errors = data.errors || {};
    throw error;
  }

  return data;
}

async function updateCartBadge() {
  const badge = document.getElementById('nav-cart-badge');
  if (!badge) return;

  const token = getToken();
  if (!token) {
    badge.textContent = '0';
    badge.classList.add('d-none');
    return;
  }

  try {
    const res = await apiFetch('/cart');
    const items = res.data?.items || [];
    const count = items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);

    const oldCount = parseInt(badge.textContent || '0');
    badge.textContent = count;
    
    if (count > 0) {
      badge.classList.remove('d-none');
    } else {
      badge.classList.add('d-none');
    }

    if (count !== oldCount && count > 0) {
      badge.classList.remove('badge-bounce');
      // Trigger reflow to restart CSS animation
      void badge.offsetWidth;
      badge.classList.add('badge-bounce');
    }
  } catch (err) {
    badge.textContent = '0';
    badge.classList.add('d-none');
  }
}

function updateAuthUI() {
  const token = getToken();
  const user = getUser();

  const guestNav = document.getElementById('nav-guest-menu');
  const userNav = document.getElementById('nav-user-menu');
  const userNameEl = document.getElementById('nav-user-name');
  const adminBtn = document.getElementById('nav-admin-dashboard-btn');

  if (token && user) {
    if (guestNav) guestNav.classList.add('d-none');
    if (userNav) userNav.classList.remove('d-none');
    if (userNameEl) userNameEl.textContent = user.name || 'Account';
    if (adminBtn) {
      if (user.role === 'admin') {
        adminBtn.classList.remove('d-none');
      } else {
        adminBtn.classList.add('d-none');
      }
    }
  } else {
    if (guestNav) guestNav.classList.remove('d-none');
    if (userNav) userNav.classList.add('d-none');
    if (adminBtn) adminBtn.classList.add('d-none');
  }
}

async function logoutUser() {
  try {
    await apiFetch('/logout', { method: 'POST' });
  } catch (e) {
    // Ignore logout error if token was already invalid
  } finally {
    setToken(null);
    setUser(null);
    window.location.href = '/login';
  }
}

// Attach globally
window.apiFetch = apiFetch;
window.getToken = getToken;
window.setToken = setToken;
window.getUser = getUser;
window.setUser = setUser;
window.updateCartBadge = updateCartBadge;
window.updateAuthUI = updateAuthUI;
window.logoutUser = logoutUser;

// Initialize state on page load
document.addEventListener('DOMContentLoaded', () => {
  updateAuthUI();
  updateCartBadge();
});
