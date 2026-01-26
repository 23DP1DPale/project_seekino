<template>
    <v-app>
        <v-app-bar class="position-sticky" color="#1E1E1E">
            <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
            <v-app-bar-title class="text-h4 font-weight-bold">SEEKINO</v-app-bar-title>
            <v-btn style="background-color:#E50914; color:white" class="register-btn">Reģistrēties</v-btn>
        </v-app-bar>
            
        <v-navigation-drawer
            v-model="drawer" 
            :permanent="!$vuetify.display.mobile"
            :temporary="$vuetify.display.mobile"
            floating=""
            color="#1E1E1E"
            class="position-fixed">
            <v-list nav>
                <v-list-item link>Filmas</v-list-item>
                <v-list-item link>Biļetes</v-list-item>
                <v-list-item link>Navigation3</v-list-item>
                <v-list-item link>Navigation4</v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-main class="bg-grey-darken-4">
            <v-container >
                <v-carousel hide-delimiters height="400" width="200" transition-duration="700" class="rounded">
                <v-carousel-item>
                    <v-sheet height="100%" color="red">
                        <div class="d-flex fill-height justify-center align-center">
                            <div class="text-h2">
                                Filma1
                            </div>
                        </div>
                    </v-sheet>
                </v-carousel-item>
                <v-carousel-item>
                    <v-sheet height="100%" color="blue">
                        <div class="d-flex fill-height justify-center align-center">
                            <div class="text-h2">
                                Filma2
                            </div>
                        </div>
                    </v-sheet>
                </v-carousel-item>
                <v-carousel-item>
                    <v-sheet height="100%" color="green">
                        <div class="d-flex fill-height justify-center align-center">
                            <div class="text-h2">
                                Filma3
                            </div>
                        </div>
                    </v-sheet>
                </v-carousel-item>
                </v-carousel>
            </v-container>
            <v-container class="bg-grey-darken-3 rounded-lg mt-7" width="100%" height="100">
                <v-select
                    label="Kārtot pēc"
                    :items="['Jaunāka', 'Lētāka', 'Dārgāka']"
                    width="300"
                ></v-select>
            </v-container>
            <v-container width="70%" height="500" class="bg-white mb-8 rounded">
                <v-card>
                    
                </v-card>
            </v-container>
        </v-main>
    </v-app>
    <v-footer color="#0D0D0D" height="50" class="justify-md-center">
            Copyright &copy 2026 D.Palejs
    </v-footer>
</template>

<script setup>
    import { ref, watch } from 'vue'

    const drawer = ref(false)
    const group = ref(null)

    watch(group, () => {
        drawer.value = false
    })
</script>