<template>
    <v-footer class="site-footer pa-0" :class="{ 'site-footer--flush': withoutTopGap }">
        <v-container class="py-10">
            <v-row class="ga-0">
                <v-col cols="12" md="4" class="pr-md-8">
                    <v-img src="/img/logo_seekino.png" max-width="160" height="52" class="logo mb-3" />
                    <p class="footer-text mb-4">
                        Mūsdienīga kino biļešu platforma ar ērtu rezervāciju, seansu pārskatu un filmu atlasi pēc
                        tavām vēlmēm.
                    </p>
                    <div class="d-flex ga-2">
                        <v-btn
                            v-for="icon in socialIcons"
                            :key="icon"
                            :icon="icon"
                            size="small"
                            variant="outlined"
                            class="footer-social-btn"
                        />
                    </div>
                </v-col>
                <v-col cols="6" md="2">
                    <h4 class="footer-heading mb-3">Navigācija</h4>
                    <v-list density="compact" class="footer-list pa-0">
                        <v-list-item v-for="link in footerNavLinks" :key="link.title" :to="link.to" :title="link.title" class="px-0" />
                    </v-list>
                </v-col>
                <v-col cols="6" md="3">
                    <h4 class="footer-heading mb-3">Lietotājiem</h4>
                    <v-list density="compact" class="footer-list pa-0">
                        <v-list-item
                            v-for="link in footerUserLinks"
                            :key="link.title"
                            :to="link.to || undefined"
                            :title="link.title"
                            class="px-0"
                        />
                    </v-list>
                </v-col>
                <v-col cols="12" md="3">
                    <h4 class="footer-heading mb-3">Kontakti</h4>
                    <p class="footer-text mb-2">Brīvības iela 100, Rīga</p>
                    <p class="footer-text mb-2">+371 2000 1234</p>
                    <p class="footer-text mb-0">info@seekino.lv</p>
                </v-col>
            </v-row>
        </v-container>
        <div class="footer-bottom w-100 py-3 px-4 text-center">
            Copyright &copy; 2026 SEEKINO. Visas tiesības aizsargātas.
        </div>
    </v-footer>
</template>

<script setup>
defineProps({
    withoutTopGap: {
        type: Boolean,
        default: false,
    },
})

const footerNavLinks = [
    { title: 'Sākums', to: '/' },
    { title: 'Filmas', to: '/filmas' },
    { title: 'Seansi', to: '/seansi' },
    { title: 'Kontakti', to: '/kontakti' },
]
const footerUserLinks = [
    { title: 'Mans profils', to: '/profile' },
    { title: 'Atbalsts' },
    { title: 'Privātuma politika' },
]
const socialIcons = ['mdi-facebook', 'mdi-instagram', 'mdi-youtube', 'mdi-twitter']
</script>

<style scoped>
.site-footer { display: flex; flex-direction: column; align-items: stretch; margin-top: 64px; border-top: 1px solid rgba(255, 255, 255, 0.12); background: linear-gradient(180deg, rgba(14, 16, 24, 0.95), rgba(9, 11, 16, 1)), radial-gradient(circle at 15% 0%, rgba(51, 76, 126, 0.2), transparent 40%); color: #f4f6fb; }
.site-footer--flush { margin-top: 0; }
.logo { filter: invert(1); }
.footer-text { color: #c5cddd; line-height: 1.55; font-size: 0.92rem; }
.footer-heading { font-size: 0.95rem; color: #ffffff; }
.footer-list { background: transparent; }
.footer-list :deep(.v-list-item-title) { color: #d4ddf1; font-size: 0.9rem; }
.footer-list :deep(.v-list-item:hover .v-list-item-title) { color: #ffffff; }
.footer-social-btn { color: #e7eeff; border-color: rgba(255, 255, 255, 0.24); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
.footer-social-btn:hover { transform: scale(1.03); box-shadow: 0 0 18px rgba(76, 114, 255, 0.12); border-color: rgba(255, 255, 255, 0.34); }
.footer-bottom { color: #9ea8bf; border-top: 1px solid rgba(255, 255, 255, 0.1); background: rgba(6, 8, 12, 0.7); font-size: 0.82rem; }
</style>
