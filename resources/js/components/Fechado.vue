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
    <div class="d-flex">
        <!-- Lista de Conversas -->
        <div class="col-4 p-4 border-end" style="background-color: rgba(0, 0, 0, 0.7); height: calc(100vh - 3.5rem);">
            <h1 class="mb-4">Chats fechados</h1>
            <div class="d-flex flex-column justify-content-start gap-3" style="height: 85%; overflow-y: auto;">
                <div v-for="chat in chatsFechados" :key="chat.id" @click="getMessage(chat.id)" class="card" style="background-color: rgba(0, 0, 0, 0.5); width: 95%;">
                    <div class="card-body">
                        <h5 class="card-title">{{ chat.assunto }}</h5>
                        <p class="card-text">
                            <strong>Criado em:</strong> {{ chat.criado_em }}
                        </p>
                        <p v-if="chat.linha" class="card-text">
                            <strong>Linha:</strong> {{ chat.linha }}
                        </p>
                        <p class="card-text">
                            <strong>Status:</strong> {{ chat.chat_status }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens do Chat -->
        <div v-if="this.chatSelecionado" class="col-8 d-flex flex-column p-3" style="background-color: rgba(0, 0, 0, 0.7); height: calc(100vh - 3rem);">
            <div v-if="avisoPublicar" class="row mb-3">
                <p class="text-white">Não esqueça de retirar/alterar possíveis identificadores sobre as pessoas e/ou palavras erradas e de baixo calão!</p>
            </div>
            <div v-else>
                <div v-if="mensagens.length" class="row justify-content-end">
                    <button @click="modoPublicarChat()" class="btn btn-success btn-sm" :disabled="loading">Tornar chat público</button>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <div v-for="(mensagem, index) in mensagens" :key="mensagem.id" class="mb-2">
                        <span v-if="!editarMensagens" class="text-white">
                            (Admin:{{ mensagem.admin_id }}) diz: {{ mensagem.mensagem }}
                        </span>
                        <div v-else>
                            <input v-model="mensagem.mensagem" class="form-control my-input" />
                            <label class="text-white">
                                Publicar
                                <input type="checkbox" v-model="publicarStatus[index]" checked />
                            </label>
                        </div>
                    </div>
                    <div v-if="editarMensagens" class="row mb-3">
                        <div class="col text-end">
                            <button @click="cancelarPublicarChat()" class="btn btn-danger btn-sm" :disabled="loading">Cancelar</button>
                            <button @click="publicarChat()" class="btn btn-success btn-sm" :disabled="loading">Publicar mensagens</button>
                        </div>
                    </div>
                    <div v-else class="row">
                        <input class="form-control col-8 my-input" type="text" v-model="novaMensagem" placeholder="Digite sua mensagem">
                        <button @click="mandarMensagem()" class="btn btn-success col" :disabled="loading">Enviar mensagem</button>
                        <!-- Dica para mensagem vazia -->
                        <div v-if="novaMensagem.trim() === ''" class="text-danger mt-2">A mensagem não pode estar vazia.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
