<script>
import { mapState, mapMutations } from 'vuex';
import axios from 'axios';

export default {
    computed: {
        ...mapState(['user']),
        nomeServidor() {
        return window.nomeServidor;
    }
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
    },
};
</script>

<template>
    <div style="background-color:#3994cc; max-width: 100%;" class="container">
        <div class="row" style="padding-top: 0px; padding-bottom: 0px;">
            <div class="col d-flex align-items-center text-center">
                <img style="height:1.5vw; margin-right: 10px; padding-left: 1vw; padding-right: 2vw;"
                    src="../../img/logo.png" alt="ESCUTA LOGO">
                <div class="vertical-divider"></div>
                <button id="botao" class="btn btn-primary" @click="setCurrentView('Painel')">PAINEL</button>
                <div class="vertical-divider"></div>
                <button id="botao" class="btn btn-primary" @click="setCurrentView('Aguardando')">AGUARDANDO</button>
                <div class="vertical-divider"></div>
                <button id="botao" class="btn btn-primary" @click="setCurrentView('Aberto')">ABERTO</button>
                <div class="vertical-divider"></div>
                <button id="botao" class="btn btn-primary" @click="setCurrentView('Fechado')">FECHADO</button>
                <div class="vertical-divider"></div>
                <button id="botao" class="btn btn-primary" @click="setCurrentView('Enquete')">ADICIONAR ENQUETE</button>
                <div class="vertical-divider"></div>
                <!-- Exibe o nome do usuário e botão de logout -->
                <div class="col text-end">
                    <span style="margin-right:10px; color:white;">Bem vindo(a), {{ nomeServidor }}</span>
                    <div class="vertical-divider"></div>
                    <a href="https://amttdetra.com/" style="margin-left:10px; color:white;" >Sair</a>
                </div>
            </div>
        </div>
    </div>
</template>
