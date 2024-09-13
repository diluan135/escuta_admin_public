<script>
import { mapState, mapMutations, mapActions } from 'vuex';
import Barra from './Barra.vue';
import Aguardando from './Aguardando.vue';
import Painel from './Painel.vue';
import Aberto from './Aberto.vue';
import Enquete from './Enquete.vue';
import Fechado from './Fechado.vue';
import Logout from './Logout.vue';
import FAQ from './FAQ.vue';

export default {
    components: { Barra },
    computed: {
        ...mapState(['currentView', 'loading']),
        currentViewComponent() {
            switch (this.currentView) {
                case 'Painel':
                    return Painel;
                case 'Aguardando':
                    return Aguardando;
                case 'Enquete':
                    return Enquete;
                case 'Aberto':
                    return Aberto;
                case 'Fechado':
                    return Fechado;
                case 'Logout':
                    return Logout;
                case 'FAQ':
                    return FAQ;
                default:
                    return Painel;
            }
        },
    },
    mounted() {
        this.setCurrentView('Painel');
        this.fetchChats();
    },
    methods: {
        ...mapMutations(['setCurrentView']),
        ...mapActions(['fetchChats']),
    },
};
</script>

<template>
    <div v-if="loading" class="loading-overlay">
        <p>Carregando dados...</p>
    </div>
    <div v-else>
        <Barra></Barra>
        <div>
            <component :is="currentViewComponent"></component>
        </div>
    </div>
</template>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
}

</style>