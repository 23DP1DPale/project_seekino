<template>
  <div class="contacts-page">
    <v-main class="main-content">
      <v-container class="py-8 py-md-10">
        <v-card class="contacts-hero pa-5 pa-md-7 mb-6">
          <p class="hero-badge mb-2">SEEKINO KONTAKTI</p>
          <h1 class="contacts-title mb-3">Sazinies ar mums</h1>
          <p class="muted-copy mb-0">
            Ja tev ir jautājumi par seansiem, rezervācijām vai lietotāja profilu, sazinies ar SEEKINO komandu.
          </p>
        </v-card>

        <v-row>
          <v-col cols="12" md="5">
            <v-card class="contacts-card pa-4 pa-md-5 h-100">
              <h2 class="panel-title mb-4">Kontaktinformācija</h2>

              <div class="contact-list">
                <div v-for="item in contactItems" :key="item.label" class="contact-row">
                  <v-icon :icon="item.icon" class="contact-icon" />
                  <div>
                    <p class="contact-label mb-1">{{ item.label }}</p>
                    <p class="contact-value mb-0">{{ item.value }}</p>
                  </div>
                </div>
              </div>
            </v-card>
          </v-col>

          <v-col cols="12" md="7">
            <v-card class="contacts-card pa-4 pa-md-5 h-100">
              <h2 class="panel-title mb-4">Raksti mums</h2>

              <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
                {{ successMessage }}
              </v-alert>

              <v-form @submit.prevent="submitMessage">
                <v-text-field
                  v-model="form.name"
                  label="Vārds"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-outline"
                  class="mb-3"
                  :error-messages="visibleFieldErrors.name"
                  @focus="hideFieldError('name')"
                />
                <v-text-field
                  v-model="form.email"
                  label="E-pasts"
                  type="email"
                  variant="outlined"
                  prepend-inner-icon="mdi-email-outline"
                  class="mb-3"
                  :error-messages="visibleFieldErrors.email"
                  @focus="hideFieldError('email')"
                />
                <v-textarea
                  v-model="form.message"
                  label="Ziņa"
                  variant="outlined"
                  rows="5"
                  prepend-inner-icon="mdi-message-text-outline"
                  class="mb-4"
                  :error-messages="visibleFieldErrors.message"
                  @focus="hideFieldError('message')"
                />
                <v-btn type="submit" color="#E50914" rounded="lg" class="text-none submit-btn">
                  Nosūtīt ziņu
                </v-btn>
              </v-form>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const contactItems = [
  { label: 'Adrese', value: 'Brīvības iela 100, Rīga', icon: 'mdi-map-marker-outline' },
  { label: 'Tālrunis', value: '+371 2000 1234', icon: 'mdi-phone-outline' },
  { label: 'E-pasts', value: 'info@seekino.lv', icon: 'mdi-email-outline' },
  { label: 'Darba laiks', value: 'Pirmdiena–Svētdiena, 10:00–22:00', icon: 'mdi-clock-outline' },
]

const form = ref({
  name: '',
  email: '',
  message: '',
})
const successMessage = ref('')
const showErrors = ref(false)
const hiddenFieldErrors = ref({
  name: false,
  email: false,
  message: false,
})
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const fieldErrors = computed(() => {
  const errors = {
    name: [],
    email: [],
    message: [],
  }

  if (!form.value.name.trim()) {
    errors.name.push('Vārds ir obligāts.')
  }

  if (!form.value.email.trim()) {
    errors.email.push('E-pasts ir obligāts.')
  } else if (!emailPattern.test(form.value.email.trim())) {
    errors.email.push('E-pastam jābūt derīgā formātā.')
  }

  if (!form.value.message.trim()) {
    errors.message.push('Ziņa ir obligāta.')
  }

  return errors
})

const isFormValid = computed(() =>
  Object.values(fieldErrors.value).every((errors) => errors.length === 0)
)

const visibleFieldErrors = computed(() => ({
  name: showErrors.value && !hiddenFieldErrors.value.name ? fieldErrors.value.name : [],
  email: showErrors.value && !hiddenFieldErrors.value.email ? fieldErrors.value.email : [],
  message: showErrors.value && !hiddenFieldErrors.value.message ? fieldErrors.value.message : [],
}))

const resetHiddenFieldErrors = () => {
  hiddenFieldErrors.value = {
    name: false,
    email: false,
    message: false,
  }
}

const hideFieldError = (field) => {
  hiddenFieldErrors.value[field] = true
}

const submitMessage = () => {
  showErrors.value = true
  resetHiddenFieldErrors()

  if (!isFormValid.value) {
    successMessage.value = ''
    return
  }

  successMessage.value = 'Ziņa nosūtīta! Mēs ar tevi sazināsimies pēc iespējas ātrāk.'
  showErrors.value = false
  resetHiddenFieldErrors()
  form.value = {
    name: '',
    email: '',
    message: '',
  }
}
</script>

<style scoped>
.contacts-page {
  background: #0a0c12;
  color: #f4f6fb;
}

.main-content {
  min-height: 100vh;
  background:
    radial-gradient(circle at 12% 18%, rgba(68, 111, 203, 0.34), transparent 42%),
    radial-gradient(circle at 82% 14%, rgba(220, 54, 88, 0.3), transparent 38%),
    radial-gradient(circle at 56% 86%, rgba(66, 141, 106, 0.22), transparent 36%),
    linear-gradient(130deg, #0f1628 0%, #17172a 45%, #2a141d 100%);
}

.contacts-hero,
.contacts-card {
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 16px;
  background: linear-gradient(165deg, rgba(18, 23, 36, 0.96), rgba(12, 15, 24, 0.96));
  color: #edf2ff;
  box-shadow: 0 22px 58px rgba(0, 0, 0, 0.34);
}

.contacts-card :deep(.v-field),
.contacts-card :deep(.v-label),
.contacts-card :deep(.v-field__input),
.contacts-card :deep(.v-icon) {
  color: #edf2ff;
}

.hero-badge {
  display: inline-block;
  padding: 6px 12px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 999px;
  color: #d7e2ff;
  font-size: 12px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.contacts-title {
  color: #ffffff;
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.05;
}

.panel-title {
  color: #ffffff;
  font-size: 1.25rem;
  font-weight: 800;
}

.muted-copy {
  color: #d2d9e7;
  line-height: 1.55;
}

.contact-list {
  display: grid;
  gap: 16px;
}

.contact-row {
  display: flex;
  gap: 14px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.contact-row:last-child {
  padding-bottom: 0;
  border-bottom: 0;
}

.contact-icon {
  flex: 0 0 auto;
  color: #ff5a44;
}

.contact-label {
  color: #aeb8cc;
  font-size: 0.9rem;
}

.contact-value {
  color: #ffffff;
  font-weight: 700;
}

.submit-btn {
  background: linear-gradient(135deg, #ff5a44, #e50914);
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 8px 26px rgba(229, 9, 20, 0.36);
}
</style>
