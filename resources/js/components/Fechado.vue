<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            loading: false,
            avisoPublicar: false,
            editarMensagens: false,
            publicarStatus: [], // Armazena o estado de publicação para cada mensagem
        }
    },
    computed: {
        ...mapState(['chatsFechados', 'user']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsFechados']),
        async getMessage(chat_id) {
            const response = await axios.get('/api/mensagem', {
                params: {
                    chat_id: chat_id,
                }
            });
            console.log(response);
            this.mensagens = response.data;
            this.chatSelecionado = chat_id;
            this.publicarStatus = this.mensagens.map(() => true); // Predefine como publicável (true) para todas as mensagens
        },
        async mandarMensagem() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post('/api/mensagem/enviarMensagem', {
                    admin_id: parseInt(idServidor, 10),
                    chat_id: this.chatSelecionado,
                    mensagem: this.novaMensagem,
                });
                console.log('Mensagem enviada:', response.data);
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
            this.editarMensagens = true; // Ativa o modo de edição
            this.avisoPublicar = true;
        },
        async publicarChat() {
            const mensagensPublicaveis = this.mensagens.map((mensagem, index) => {
                const mensagemPublicada = {
                    admin_id: mensagem.admin_id,
                    mensagem: this.publicarStatus[index] ? mensagem.mensagem : '',
                };

                console.log("Mensagem Publicável:", mensagemPublicada);

                return mensagemPublicada;
            });

            try {
                console.log("Mensagens a serem publicadas:", mensagensPublicaveis);

                const response = await axios.post('/api/chat/publicarChat', {
                    chat_id: this.chatSelecionado,
                    mensagens: mensagensPublicaveis,
                });

                console.log('Chat publicado:', response.data);
                this.chatSelecionado = null;
                this.editarMensagens = false; // Desativa o modo de edição após a publicação
                this.avisoPublicar = false;
            } catch (error) {
                console.error('Erro ao publicar chat:', error);
            }
        },

        cancelarPublicarChat() {
            this.avisoPublicar = false;
            this.editarMensagens = false; // Desativa o modo de edição
        }
    },
    mounted() {
        this.fetchChatsFechados();
        this.avisoPublicar = false;
    },
};
</script>

<template>
    <h2>FECHADO</h2>
    <div class="d-flex">
        <div class="col-4">
            <h1>Conversas</h1>
            <div v-for="chat in chatsFechados " :key="chat.id">
                <div>{{ chat.assunto }}</div>
                <div>{{ chat.criado_em }}</div>
                <div v-if="chat.linha != null">{{ chat.linha }}</div>
                <div>{{ chat.chat_status }}</div>
                <button @click="getMessage(chat.id)">Acessar chat</button>
                <br>
                <br>
            </div>
        </div>
        <div class="col-8">
            <div class="row" v-if="this.avisoPublicar">
                <p>Não esqueça de retirar/alterar possíveis identificadores sobre as pessoas
                    e/ou palavras erradas e de baixo calão!</p>
            </div>
            <div v-else>
                <div v-if="mensagens.length" class="row justify-content-end">
                    <button @click="modoPublicarChat()" class="col-2" :disabled="loading">Tornar chat público</button>
                </div>
            </div>
            <div class="row">
                <div v-if="mensagens.length" class="col-8">

                    <div v-for="(mensagem, index) in mensagens" :key="mensagem.id">
                        <div v-if="editarMensagens">
                            <span v-if="mensagem.usuario_id"> Usuário: </span>
                            <span v-if="mensagem.admin_id"> Admin:{{ mensagem.admin_id }}</span>
                            <input v-model="mensagem.mensagem" />
                            <label>
                                Publicar
                                <input type="checkbox" v-model="publicarStatus[index]" checked />
                            </label>
                        </div>
                        <div v-else>
                            <span v-if="mensagem.usuario_id"> ( Usuário:{{ mensagem.usuario_id }} ) diz: {{
                                mensagem.mensagem }}</span>
                            <br>
                            <span v-if="mensagem.admin_id"> ( Admin:{{ mensagem.admin_id }} ) diz: {{ mensagem.mensagem
                                }}</span>
                        </div>
                    </div>

                    <div v-if="this.avisoPublicar" class="row">
                        <div class="col text-end">
                            <button style="margin-right: 10px;" @click="cancelarPublicarChat()" class="col-2"
                                :disabled="loading">Cancelar</button>
                            <button @click="publicarChat()" class="col-2" :disabled="loading">Publicar
                                mensagens</button>
                        </div>
                    </div>
                    <div v-else class="row">
                        <input class="col-8" type="text" v-model="novaMensagem">
                        <button @click="mandarMensagem()" class="col" :disabled="loading">Enviar mensagem</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
