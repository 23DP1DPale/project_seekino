<template>
  <v-app class="auth-page">

    <v-main class="auth-main">
      <v-container class="auth-container py-10 py-md-14">
        <v-card class="auth-card pa-5 pa-md-7 rounded-xl">
          <p class="auth-badge">Jauns SEEKINO konts</p>
          <h1 class="auth-title mb-2">Reģistrēties</h1>
          <p class="auth-copy mb-6">Izveido kontu, lai saglabātu rezervācijas un piekļūtu lietotāja datiem.</p>

          <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
            {{ error }}
          </v-alert>

          <v-form @submit.prevent="submit">
            <v-text-field
              v-model="form.nickname"
              label="Segvārds"
              variant="outlined"
              prepend-inner-icon="mdi-account-outline"
              class="mb-3"
              :disabled="authLoading"
            />
            <v-text-field
              v-model="form.email"
              label="E-pasts"
              type="email"
              variant="outlined"
              prepend-inner-icon="mdi-email-outline"
              class="mb-3"
              :disabled="authLoading"
            />
            <v-text-field
              v-model="form.password"
              label="Parole"
              type="password"
              variant="outlined"
              prepend-inner-icon="mdi-lock-outline"
              class="mb-3"
              :disabled="authLoading"
            />
            <v-text-field
              v-model="form.password_confirmation"
              label="Paroles apstiprinājums"
              type="password"
              variant="outlined"
              prepend-inner-icon="mdi-lock-check-outline"
              class="mb-4"
              :disabled="authLoading"
            />

            <v-btn
              type="submit"
              color="#E50914"
              block
              rounded="lg"
              size="large"
              class="text-none auth-submit"
              :loading="authLoading"
            >
              Reģistrēties
            </v-btn>
          </v-form>

          <p class="auth-footnote mt-5 mb-0">
            Jau ir konts?
            <RouterLink to="/login" class="auth-link">Pieslēgties</RouterLink>
          </p>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuth } from '@/services/auth'

const router = useRouter()
const { authLoading, register } = useAuth()
const form = ref({
  nickname: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const error = ref('')

const isEmailValid = (value) => /^\S+@\S+\.\S+$/.test(value)

const submit = async () => {
  error.value = ''

  if (!form.value.nickname || !form.value.email || !form.value.password || !form.value.password_confirmation) {
    error.value = 'Lūdzu aizpildi visus reģistrācijas laukus.'
    return
  }

  if (form.value.nickname.length > 50) {
    error.value = 'Segvārds nedrīkst pārsniegt 50 rakstzīmes.'
    return
  }

  if (!isEmailValid(form.value.email)) {
    error.value = 'E-pasta adrese nav pareiza.'
    return
  }

  if (form.value.password.length < 8) {
    error.value = 'Parolei jābūt vismaz 8 rakstzīmes garai.'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Paroles apstiprinājums nesakrīt.'
    return
  }

  try {
    await register(form.value)
    router.push('/')
  } catch (err) {
    error.value = err.message || 'Reģistrācija neizdevās.'
  }
}
</script>

<style scoped>
.auth-page {
  background:
    radial-gradient(circle at 18% 14%, rgba(50, 83, 148, 0.34), transparent 34%),
    radial-gradient(circle at 82% 18%, rgba(176, 38, 68, 0.28), transparent 32%),
    #0a0c12;
  color: #f4f6fb;
}

.auth-main {
  min-height: calc(100vh - 76px);
}

.auth-container {
  display: grid;
  min-height: calc(100vh - 76px);
  place-items: center;
}

.auth-card {
  width: min(100%, 560px);
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: linear-gradient(165deg, rgba(18, 23, 36, 0.96), rgba(12, 15, 24, 0.96));
  color: #edf2ff;
  box-shadow: 0 22px 58px rgba(0, 0, 0, 0.38);
}

.auth-card :deep(.v-field),
.auth-card :deep(.v-label),
.auth-card :deep(.v-field__input),
.auth-card :deep(.v-icon),
.auth-card :deep(.v-alert__content) {
  color: #edf2ff;
}

.auth-badge {
  display: inline-block;
  margin-bottom: 10px;
  padding: 6px 12px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 999px;
  color: #d7e2ff;
  font-size: 12px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.auth-title {
  font-size: clamp(2rem, 5vw, 3rem);
  line-height: 1.05;
}

.auth-copy,
.auth-footnote {
  color: #d2d9e7;
}

.auth-link {
  color: #ff5a44;
  font-weight: 700;
  text-decoration: none;
}

.auth-link:hover {
  text-decoration: underline;
}
</style>
