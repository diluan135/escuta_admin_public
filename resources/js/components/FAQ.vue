<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            faqChats: [], // Armazena os chats do FAQ
            mensagensFAQ: [], // Armazena as mensagens do FAQ
            chatSelecionado: null,
            loading: false,
            editarMensagens: false, // Controle do modo de edição
            publicarStatus: [], // Armazena o status de publicação das mensagens
        };
    },
    computed: {
        ...mapState(['faq', 'user']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchFAQ']),
        async getMensagensFAQ(chat) {
            console.log('entrou');
            
            try {
                const response = await axios.get('/api/FAQ/mensagensPublicadas', {
                    params: { chat_id: chat.id }
                });
                this.mensagensFAQ = response.data;
                this.chatSelecionado = chat.id;
                this.publicarStatus = this.mensagensFAQ.map(mensagem => mensagem.publicado === 1);
                console.log(response);
                
            } catch (error) {
                console.error('Erro ao obter mensagens do FAQ:', error);
            }
            console.log('saiu');
            
        },
        modoEditarMensagens() {
            this.editarMensagens = true;
        },
        async publicarMensagensFAQ() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            const mensagensPublicaveis = this.mensagensFAQ.map((mensagem, index) => ({
                admin_id: mensagem.admin_id,
                mensagem: mensagem.mensagem,
                publicado: this.publicarStatus[index] ? 1 : 0,
            }));

            try {
                await axios.post('/api/FAQ/publicarMensagens', {
                    tipo: this.chatTipo,
                    assunto: this.chatAssunto,
                    linha: this.chatLinha,
                    mensagens: mensagensPublicaveis,
                });
            } catch (error) {
                console.error('Erro ao publicar mensagens do FAQ:', error);
            } finally {
                this.loading = false;
                this.editarMensagens = false; // Desativa o modo de edição
            }
        },
        cancelarEdicaoFAQ() {
            this.editarMensagens = false; // Desativa o modo de edição
        },
    },
    mounted() {
        this.fetchFAQ().then(() => {
            this.faqChats = this.$store.state.faq;
        });
    }
};
</script>

<template>
    <h2>FAQ</h2>
    <div class="d-flex">
        <div class="col-4">
            <h1>Chats do FAQ</h1>
            <div v-for="chat in faqChats" :key="chat.id">
                <div>{{ chat.assunto }}</div>
                <div>{{ chat.criado_em }}</div>
                <div v-if="chat.linha">{{ chat.linha }}</div>
                <div>{{ chat.chat_status }}</div>
                <button @click="getMensagensFAQ(chat)">Acessar mensagens do FAQ</button>
                <br><br>
            </div>
        </div>
        <div v-if="chatSelecionado" class="col-8">
            <div v-if="editarMensagens">
                <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id">
                    <input v-model="mensagem.mensagem" class="form-control" />
                    <label>
                        Publicar
                        <input type="checkbox" v-model="publicarStatus[index]" />
                    </label>
                </div>
                <div class="row">
                    <div class="col text-end">
                        <button @click="cancelarEdicaoFAQ()" class="col-2" :disabled="loading">Cancelar</button>
                        <button @click="publicarMensagensFAQ()" class="col-2" :disabled="loading">Publicar
                            mensagens</button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-if="mensagensFAQ.length">
                    <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id">
                        (Admin:{{ mensagem.admin_id }}) diz: {{ mensagem.mensagem }}
                        <div v-if="mensagem.publicado === 0">
                            <label>
                                Editar
                                <button @click="modoEditarMensagens()" class="btn btn-secondary">Editar</button>
                            </label>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p>Selecione um chat para visualizar as mensagens.</p>
                </div>
            </div>
        </div>
    </div>
</template>
