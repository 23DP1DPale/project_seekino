import { computed, ref } from 'vue'

export const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000'

const token = ref(localStorage.getItem('seekino_token') || '')
const user = ref(readStoredUser())
const authLoading = ref(false)

function readStoredUser() {
  try {
    const storedUser = localStorage.getItem('seekino_user')

    return storedUser ? JSON.parse(storedUser) : null
  } catch {
    localStorage.removeItem('seekino_user')

    return null
  }
}

function storeSession(accessToken, nextUser) {
  token.value = accessToken
  user.value = nextUser
  localStorage.setItem('seekino_token', accessToken)
  localStorage.setItem('seekino_user', JSON.stringify(nextUser))
}

function clearSession() {
  token.value = ''
  user.value = null
  localStorage.removeItem('seekino_token')
  localStorage.removeItem('seekino_user')
}

async function requestJson(path, options = {}) {
  const response = await fetch(`${apiBaseUrl}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  })
  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    throw new Error(errorMessage(payload) || 'Pieprasījumu neizdevās izpildīt.')
  }

  return payload
}

function errorMessage(payload) {
  if (payload?.kļūdas && typeof payload.kļūdas === 'object') {
    const firstError = Object.values(payload.kļūdas).flat().find(Boolean)

    if (firstError) {
      return firstError
    }
  }

  return payload?.ziņa || payload?.message || ''
}

async function fetchMe() {
  if (!token.value) {
    return null
  }

  const payload = await requestJson('/api/me', {
    headers: {
      Authorization: `Bearer ${token.value}`,
    },
  })

  user.value = payload.lietotājs
  localStorage.setItem('seekino_user', JSON.stringify(payload.lietotājs))

  return payload.lietotājs
}

async function login(credentials) {
  authLoading.value = true

  try {
    const payload = await requestJson('/api/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    })
    const accessToken = payload.tokens?.access_token

    if (!accessToken) {
      throw new Error('Serveris neatgrieza autentifikācijas tokenu.')
    }

    token.value = accessToken
    localStorage.setItem('seekino_token', accessToken)

    const currentUser = await fetchMe()
    storeSession(accessToken, currentUser)

    return currentUser
  } finally {
    authLoading.value = false
  }
}

async function register(details) {
  authLoading.value = true

  try {
    const payload = await requestJson('/api/register', {
      method: 'POST',
      body: JSON.stringify(details),
    })
    const accessToken = payload.tokens?.access_token

    if (accessToken) {
      token.value = accessToken
      localStorage.setItem('seekino_token', accessToken)

      const currentUser = await fetchMe()
      storeSession(accessToken, currentUser)

      return currentUser
    }

    return payload.lietotājs || null
  } finally {
    authLoading.value = false
  }
}

async function logout() {
  const currentToken = token.value

  if (!currentToken) {
    clearSession()

    return
  }

  authLoading.value = true

  try {
    await requestJson('/api/logout', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${currentToken}`,
      },
    })
  } catch {
    // Lokālo sesiju notīrām arī tad, ja servera logout pieprasījums neizdodas.
  } finally {
    authLoading.value = false
    clearSession()
  }
}

export function useAuth() {
  return {
    token,
    user,
    authLoading,
    isAuthenticated: computed(() => Boolean(token.value && user.value)),
    login,
    register,
    fetchMe,
    logout,
    clearSession,
  }
}
