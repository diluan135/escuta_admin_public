<script>
import { mapState, mapMutations } from 'vuex';
import axios from 'axios';

export default {
    computed: {
        ...mapState(['user', 'currentView']),
        nomeServidor() {
            return window.nomeServidor;
        },
    },
    data() {
        return {
            isLoggingOut: false,
        };
    },
    methods: {
        ...mapMutations(['setCurrentView']),
        async logout() {
            if (this.isLoggingOut) return;
            this.isLoggingOut = true;
            try {
                await axios.post('/logout');
                this.user = null;
                window.location.href = '/';
            } catch (error) {
                console.error('Logout failed', error);
            } finally {
                this.isLoggingOut = false;
            }
        },
        getButtonClass(view) {
            return this.currentView === view ? 'btn btn-primary active' : 'btn btn-primary';
        },
    },
};
</script>

<template>
    <div style="background-color:#141932; max-width: 100%;" class="container">
        <div class="row" style="padding-top: 0px; padding-bottom: 0px;">
            <div class="col d-flex align-items-center text-center">
                <img style="height:1.5vw; margin-right: 10px; padding-left: 1vw; padding-right: 2vw;"
                    src="../../img/logo.png" alt="ESCUTA LOGO">
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('Painel')" @click="setCurrentView('Painel')">PAINEL</button>
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('Aguardando')" @click="setCurrentView('Aguardando')">AGUARDANDO</button>
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('Aberto')" @click="setCurrentView('Aberto')">ABERTO</button>
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('Fechado')" @click="setCurrentView('Fechado')">FECHADO</button>
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('FAQ')" @click="setCurrentView('FAQ')">FAQ</button>
                <div class="vertical-divider"></div>
                <button id="botao" :class="getButtonClass('Enquete')" @click="setCurrentView('Enquete')">ADICIONAR ENQUETE</button>
                <div class="vertical-divider"></div>
                <!-- Exibe o nome do usuário e botão de logout -->
                <div class="col text-end">
                    <span style="margin-right:10px; color:white;">Bem vindo(a), {{ nomeServidor }}</span>
                    <div class="vertical-divider"></div>
                    <a href="https://amttdetra.com/" style="margin-left:10px; color:white;" @click.prevent="logout">Sair</a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn.active {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
}
</style>
    