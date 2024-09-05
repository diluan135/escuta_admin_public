<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            chatTipo: null,
            chatAssunto: null,
            chatLinha: null,
            loading: false,
            avisoPublicar: false,
            editarMensagens: false,
            publicarStatus: []  // Armazena o status de publicação das mensagens
        };
    },
    computed: {
        ...mapState(['chatsFechados', 'user']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsFechados']),
        async getMessage(chat) {
            const response = await axios.get('/api/mensagem', {
                params: { chat_id: chat.id }
            });
            this.mensagens = response.data;
            this.chatSelecionado = chat.id;
            this.chatTipo = chat.tipo;
            this.chatAssunto = chat.assunto;
            this.chatLinha = chat.linha;
            this.publicarStatus = this.mensagens.map(() => true);  // Inicializa com todas as mensagens publicáveis

        },
        async mandarMensagem() {
            if (!this.chatSelecionado || !this.novaMensagem) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post('/api/mensagem/enviarMensagem', {
                    admin_id: parseInt(this.idServidor, 10),
                    chat_id: this.chatSelecionado,
                    mensagem: this.novaMensagem,
                });
                this.novaMensagem = '';
                await this.getMessage(this.chatSelecionado);
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
            } finally {
                this.loading = false;
            }
        },
        modoPublicarChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }
            this.avisoPublicar = true;
            this.editarMensagens = true; // Ativa o modo de edição
        },
        async publicarChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;
            console.log(this.chatSelecionado, this.chatTipo, this.chatAssunto, this.chatLinha);

            const mensagensPublicaveis = this.mensagens.map((mensagem, index) => ({
                admin_id: mensagem.admin_id,
                mensagem: mensagem.mensagem,
                publicado: this.publicarStatus[index] ? 1 : 0,
            }));
            
            try {
                await axios.post('/api/FAQ/publicarChat', {
                    chat_id: this.chatSelecionado,
                    tipo: this.chatTipo,
                    assunto: this.chatAssunto,
                    linha: this.chatLinha,
                    mensagens: mensagensPublicaveis,
                });

                await this.$store.dispatch('fetchFAQ');
            } catch (error) {
                console.error('Erro ao publicar chat:', error);
                this.chatSelecionado = null;
                this.editarMensagens = false;
                this.avisoPublicar = false;
            } finally {
                this.loading = false;
                this.chatSelecionado = null;
                this.editarMensagens = false;
                this.avisoPublicar = false;
            }
        }, cancelarPublicarChat() {
            this.avisoPublicar = false;
            this.editarMensagens = false; // Desativa o modo de edição
        }
    },
    mounted() {
        this.fetchChatsFechados();
        this.avisoPublicar = false;
    }
};
</script>

<template>
    <h2>FECHADO</h2>
    <div class="d-flex">
        <div class="col-4">
            <h1>Conversas</h1>
            <div v-for="chat in chatsFechados" :key="chat.id">
                <div>{{ chat.assunto }}</div>
                <div>{{ chat.criado_em }}</div>
                <div v-if="chat.linha">{{ chat.linha }}</div>
                <div>{{ chat.chat_status }}</div>
                <button @click="getMessage(chat)">Acessar chat</button>
                <br><br>
            </div>
        </div>
        <div v-if="this.chatSelecionado" class="col-8">
            <div v-if="avisoPublicar" class="row">
                <p>Não esqueça de retirar/alterar possíveis identificadores sobre as pessoas e/ou palavras erradas e de
                    baixo calão!</p>
            </div>
            <div v-else>
                <div v-if="mensagens.length" class="row justify-content-end">
                    <button @click="modoPublicarChat()" class="col-2" :disabled="loading">Tornar chat público</button>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <div v-for="(mensagem, index) in mensagens" :key="mensagem.id">
                        <span v-if="!editarMensagens">
                            (Admin:{{ mensagem.admin_id }}) diz: {{ mensagem.mensagem }}
                        </span>
                        <div v-else>
                            <input v-model="mensagem.mensagem" class="form-control" />
                            <label>
                                Publicar
                                <input type="checkbox" v-model="publicarStatus[index]" checked />
                            </label>
                        </div>
                    </div>
                    <div v-if="editarMensagens" class="row">
                        <div class="col text-end">
                            <button @click="cancelarPublicarChat()" class="col-2" :disabled="loading">Cancelar</button>
                            <button @click="publicarChat()" class="col-2" :disabled="loading">Publicar
                                mensagens</button>
                        </div>
                    </div>
                    <div v-else class="row">
                        <input class="col-8" type="text" v-model="novaMensagem">
                        <button @click="mandarMensagem()" class="col" :disabled="loading">Enviar mensagem</button>
                        <!-- davi deixa um jeito bonitinho de exibir que não da pra mandar mensagem vazia caso o usuário tente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
