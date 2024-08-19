<script>
import axios from '../axiosConfig.js';
import Barra from './Barra.vue';
import { mapMutations } from 'vuex';

export default {
    components: { Barra },
    data() {
        return {
            tipoSolicitacao: '',
            linhasData: [],
            linhaSelecionada: '',
            assunto: '',
            mensagem: '',
        };
    },
    methods: {
        ...mapMutations(['setCurrentView']),
        clickHandler() {
            console.log('click');
            this.$router.push('/teste');
        },
        settipoSolicitacao(tipoSolicitacao) {
            this.tipoSolicitacao = tipoSolicitacao;
        },
        async fetchUser() {
            try {
                const response = await axios.get('/api/user'); // Rota para obter informações do usuário
                this.user = response.data;
                console.log(this.user.id);
            } catch (error) {
                console.error('Failed to fetch user', error);
            }
        },
        async fetchLinhasData() {
            try {
                const response = await axios.get('/api/linhas');
                this.linhasData = response.data.map(item => item.nome);
                console.log(this.linhasData);
            } catch (error) {
                console.error('Failed to fetch Linhas data', error);
            }
        },

        setLinha(linha) {
            this.linhaSelecionada = linha;
        },

        enviar() {
            console.log('usuario: ', this.user.id, 'assunto: ', this.assunto, 'mensagem: ', this.mensagem, 'linha selecionada: ', this.linhaSelecionada, 'tipo: ', this.tipo, 'tipoSolicitação: ', this.tipoSolicitacao);
            axios.post('/api/chat/newChat',
                {
                    usuario_id: this.user.id,
                    tipo: this.tipoSolicitacao,
                    assunto: this.assunto,
                    linha: this.linhaSelecionada
                }
            )
                .then(response => {
                    console.log(response.data);
                    console.log(response.data.id);
                    axios.post('api/mensagem/enviarMensagem',
                        {
                            usuario_id: this.user.id,
                            chat_id: response.data.id,
                            mensagem: this.mensagem
                        }
                    ).then(response => {
                        this.setCurrentView('MinhasSolicitacoes');
                    }).catch(error => {
                        console.error(error);
                    });

                })
                .catch(error => {
                    console.error(error);
                });
        }
    },
    mounted() {
        this.fetchUser();
        this.fetchLinhasData();
    }

}
</script>

<template>
    <div class="container text-center">
        <div class="row">
            <div class="col-6">
                <button class="btn btn-secondary dropdown-toggle" type="dropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    {{ tipoSolicitacao ? tipoSolicitacao : 'Selecione o tipo de solicitação' }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" @click="settipoSolicitacao('Linha')">Linha</a></li>
                    <li><a class="dropdown-item" @click="settipoSolicitacao('Horários')">Horários</a></li>
                    <li><a class="dropdown-item" @click="settipoSolicitacao('Sugestão')">Sugestão</a></li>
                    <li><a class="dropdown-item" @click="settipoSolicitacao('Reclamação')">Reclamação</a></li>
                    <li><a class="dropdown-item" @click="settipoSolicitacao('Outros')">Outros</a></li>
                </ul>
            </div>
            <div v-if="tipoSolicitacao == 'Linha' || tipoSolicitacao == 'Horários'" class="col-6">
                <button class="btn btn-secondary dropdown-toggle" type="dropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    {{ linhaSelecionada ? linhaSelecionada : 'Selecione a Linha' }}
                </button>
                <ul class="dropdown-menu">
                    <li v-for="(linha, index) in linhasData"><a class="dropdown-item"
                            @click="setLinha(linha)">{{ linha }}</a></li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <input v-model="assunto" placeholder="Assunto" type="text">
            </div>
        </div>
        <div class="row">
            <input v-model="mensagem" placeholder="Mensagem" type="text">
        </div>
        <div class="row">
            <button @click="enviar()">ENVIAR</button>
        </div>
    </div>
</template>