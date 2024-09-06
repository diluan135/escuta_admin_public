<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            faqChats: [], // Armazena os chats do FAQ
            mensagensFAQ: [], // Armazena as mensagens do FAQ
            chatSelecionado: null,
            chatTipo: null,
            chatAssunto: null,
            chatLinha: null,
            loading: false,
            editarMensagens: false, // Controle do modo de edição
            publicarStatus: [], // Armazena o status de publicação das mensagens
            avisoPublicar: false, // Aviso para verificar as mensagens antes de publicar
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
                this.chatTipo = chat.tipo;
                this.chatAssunto = chat.assunto;
                this.chatLinha = chat.linha;
                this.publicarStatus = this.mensagensFAQ.map(() => true); // Inicializa com todas as mensagens publicáveis
                console.log(response);
            } catch (error) {
                console.error('Erro ao obter mensagens do FAQ:', error);
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
        editarTitulo(tituloAtual) {
            // Solicita um novo título ao usuário
            const novoTitulo = prompt('Insira o novo título do chat:', tituloAtual);

            // Se o usuário inserir um novo título, faz a atualização
            if (novoTitulo && novoTitulo !== tituloAtual) {
                this.chatAssunto = novoTitulo; // Atualiza o título localmente

                // Envia uma requisição para atualizar o título no backend
                axios.post('/api/FAQ/editarTitulo', {
                    chat_id: this.chatSelecionado,
                    novo_titulo: novoTitulo
                })
                    .then(response => {
                        console.log('Título atualizado com sucesso:', response.data.message);
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar o título:', error);
                    });
            }
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
                const response = await axios.post('/api/FAQ/publicarMensagens', {
                    tipo: this.chatTipo,
                    assunto: this.chatAssunto,
                    linha: this.chatLinha,
                    mensagens: mensagensPublicaveis,
                    chat_id: this.chatSelecionado,
                });

                console.log(response.data.message);
                await this.$store.dispatch('fetchFAQ');
            } catch (error) {
                if (error.response && error.response.status === 400) {
                    console.error('Erro:', error.response.data.message);
                    alert(error.response.data.message); // Exibe a mensagem de erro para o usuário
                } else {
                    console.error('Erro ao publicar mensagens do FAQ:', error);
                }
            } finally {
                this.loading = false;
                this.editarMensagens = false; // Desativa o modo de edição
                this.avisoPublicar = false;
            }
        },
        cancelarPublicarChat() {
            this.avisoPublicar = false;
            this.editarMensagens = false; // Desativa o modo de edição
        },
    },
    mounted() {
        this.fetchFAQ();
    }
};
</script>

<template>
    <h2>FAQ</h2>
    <div class="d-flex">
        <div class="col-4">
            <h1>Chats do FAQ</h1>
            <div v-for="chat in faq" :key="chat.id">
                <div>{{ chat.assunto }}</div>
                <div>{{ chat.criado_em }}</div>
                <div v-if="chat.linha">{{ chat.linha }}</div>
                <div>{{ chat.chat_status }}</div>
                <button @click="getMensagensFAQ(chat)">Acessar mensagens do FAQ</button>
                <br><br>
            </div>
        </div>
        <div v-if="chatSelecionado" class="col-8">
            <div v-if="avisoPublicar" class="row">
                <p>Não esqueça de retirar/alterar possíveis identificadores sobre as pessoas e/ou palavras erradas e de
                    baixo calão!</p>
            </div>
            <div v-else>
                <div v-if="mensagensFAQ.length" class="row justify-content-end">
                    <h2 class="col">{{ this.chatAssunto }}</h2>
                    <button class="col-2" @click="editarTitulo(this.chatAssunto)">Editar título</button>
                    <button @click="modoPublicarChat()" class="col-2" :disabled="loading">Editar chat</button>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id">
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
                            <button @click="publicarMensagensFAQ()" class="col-2" :disabled="loading">Publicar
                                mensagens</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
