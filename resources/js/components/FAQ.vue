<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';


export default {
    data() {
        return {
            faqChats: [], // Armazena os chats do FAQ
            mensagensFAQ: [], // Armazena as mensagens do FAQ
            chatSelecionado: null,
            chatPublicado: null,
            chatPublicadoTemp: null,
            chatTipo: null,
            chatAssunto: null,
            chatLinha: null,
            loading: false,
            editarMensagens: false, // Controle do modo de edição
            editarTituloAtivo: false, // Controle do modo de edição do título
            novoTitulo: '', // Armazena o novo título durante a edição
            publicarStatus: [], // Armazena o status de publicação das mensagens
            avisoPublicar: false, // Aviso para verificar as mensagens antes de publicar
            loadingstats: 0,
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
            this.editarMensagens = false;
            this.avisoPublicar = false;
            this.loadingstats = 1;
            try {
                const response = await axios.get('/api/FAQ/mensagensPublicadas', {
                    params: { chat_id: chat.id }
                });
                this.mensagensFAQ = response.data;
                this.chatSelecionado = chat.id;
                this.chatPublicado = chat.publicado;
                this.chatTipo = chat.tipo;
                this.chatAssunto = chat.assunto;
                this.chatLinha = chat.linha;
                this.publicarStatus = this.mensagensFAQ.map(() => true); // Inicializa com todas as mensagens publicáveis
                console.log(response);
                this.loadingstats = 0;
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
        editarTitulo() {
            // Ativa o modo de edição e preenche o campo de input com o título atual
            this.editarTituloAtivo = true;
            this.novoTitulo = this.chatAssunto;
        },
        async salvarTitulo() {
            if (!this.novoTitulo || this.novoTitulo.trim() === '' || this.novoTitulo.length < 3) {
                alert('O título deve ter pelo menos 3 caracteres.');
                return;
            }

            // Se o título for válido, atualiza o título localmente e envia ao backend
            if (this.novoTitulo !== this.chatAssunto) {
                this.chatAssunto = this.novoTitulo; // Atualiza o título localmente
                console.log(this.chatSelecionado, this.novoTitulo);

                await axios.post('/api/FAQ/editarTitulo', {
                    chat_id: this.chatSelecionado,
                    novo_titulo: this.novoTitulo
                })
                    .then(response => {
                        console.log('Título atualizado com sucesso:', response.data.message);
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar o título:', error);
                    });
            }
            this.editarTituloAtivo = false; // Desativa o modo de edição do título
            this.fetchFAQ();
            // davi coloque um balãozinho com notificação que deu certo a alteração do título aqui:


        },
        cancelarEdicaoTitulo() {
            this.editarTituloAtivo = false; // Desativa o modo de edição sem salvar
            this.novoTitulo = ''; // Limpa o campo de input
        },
        async publicarMensagensFAQ() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            if ((this.chatPublicado == 1 && this.chatPublicadoTemp == false) || (this.chatPublicado == 0 && this.chatPublicadoTemp == true)) {
                try {
                    if (this.chatPublicadoTemp) {
                        await axios.post('/api/FAQ/ativarChat', {
                            chat_id: this.chatSelecionado
                        });
                    } else {
                        await axios.post('/api/FAQ/desativarChat', {
                            chat_id: this.chatSelecionado
                        });
                    }
                    await this.$store.dispatch('fetchFAQ');
                } catch (error) {
                    console.error('Erro ao atualizar o status do chat:', error);
                }

            }

            this.loading = true;

            const mensagensPublicaveis = this.mensagensFAQ.map((mensagem, index) => ({
                id: mensagem.id,
                mensagem: mensagem.mensagem,
                chatPublicado_id: this.chatSelecionado,
                publicado: this.publicarStatus[index] ? 1 : 0,
            }));

            try {
                const response = await axios.post('/api/FAQ/atualizarMensagens', {
                    tipo: this.chatTipo,
                    assunto: this.chatAssunto,
                    linha: this.chatLinha,
                    mensagens: mensagensPublicaveis,
                    chat_id: this.chatSelecionado,
                });

                console.log(response.data.message);

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
            await this.$store.dispatch('fetchFAQ');
            this.editarMensagens = false;
            this.chatSelecionado = null;
            this.getMensagensFAQ(this.chatSelecionado);

            // davi coloque um balãozinho com notificação que deu certo a alteração das mensagens aqui:

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
    <div class="d-flex">
        <!-- Lista de Chats do FAQ -->
        <div class="col-4 p-4 border-end" style="height: calc(100vh - 3.5rem); background-color: rgba(0, 0, 0, 0.7);">
            <h1 class="mb-4">FAQ</h1>
            <div class="d-flex flex-column justify-content-start gap-3" style="height: 85%; overflow-y: auto;">
                <div v-for="chat in faq" :key="chat.id" class="card" @click="getMensagensFAQ(chat)"
                    style="background-color: rgba(0, 0, 0, 0.5); width: 95%;">
                    <div class="card-body">
                        <h5 class="card-title">{{ chat.assunto }}</h5>
                        <p class="card-text">
                            <strong>Criado em:</strong> {{ chat.criado_em }}<br>
                            <span v-if="chat.linha"><strong>Linha:</strong> {{ chat.linha }}</span><br>
                            <span v-if="chat.publicado == 1" class="badge bg-success mb-4">Publicado</span>
                        </p>
                        <!-- <button @click="getMensagensFAQ(chat)" class="btn btn-primary">Acessar mensagens do FAQ</button> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens do FAQ -->
        <div v-if="chatSelecionado" class="col-8 d-flex flex-column p-3"
            style="height: calc(100vh - 3.5rem); width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7);">
            <div v-if="avisoPublicar" class="alert alert-warning">
                Não esqueça de retirar/alterar possíveis identificadores sobre as pessoas e/ou palavras erradas e de
                baixo calão!
            </div>

            <!-- Edição de Título -->
            <div v-else>
                <div class="d-flex justify-content-between align-items-center mb-3" v-if="!editarTituloAtivo">
                    <h2 class="text-white">{{ chatAssunto }}</h2>
                    <div>
                        <button class="btn btn-secondary me-2" @click="editarTitulo">Editar título</button>
                        <button class="btn btn-info" @click="modoPublicarChat()" :disabled="loading">Editar
                            chat</button>
                    </div>
                </div>
                <div v-else class="d-flex">
                    <input v-model="novoTitulo" class="form-control me-2" placeholder="Digite o novo título">
                    <button class="btn btn-success me-2" @click="salvarTitulo">Salvar</button>
                    <button class="btn btn-danger" @click="cancelarEdicaoTitulo">Cancelar</button>
                </div>
            </div>

            <!-- Lista de Mensagens -->
            <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mt-4">
                <div v-if="!editarMensagens">
                    <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id"
                        class="alert alert-secondary text-white mb-3">
                        <div v-if="mensagem.admin_id" class="d-flex justify-content-between">
                            <span><strong>Admin {{ mensagem.admin_id }}:</strong> {{ mensagem.mensagem }}</span>
                            <span><strong>{{ mensagem.publicado ? 'Publicado' : 'Não publicado' }}</strong></span>
                        </div>
                        <div v-else class="d-flex justify-content-between">
                            <span><strong>Usuário:</strong> {{ mensagem.mensagem }}</span>
                            <span><strong>{{ mensagem.publicado ? 'Publicado' : 'Não publicado' }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Modo de Edição das Mensagens -->
                <div v-else>
                    <label class="form-check mb-3">
                        <input type="checkbox" :checked="chatPublicado == 1" class="form-check-input"
                            @change="chatPublicadoTemp = !chatPublicadoTemp">
                        <span class="form-check-label">Chat publicado?</span>
                    </label>

                    <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id" class="alert alert-dark mb-3">
                        <input v-model="mensagem.mensagem" class="form-control mb-2" />
                        <label class="form-check">
                            <input type="checkbox" :checked="mensagem.publicado == 1" v-model="publicarStatus[index]"
                                class="form-check-input" />
                            <span class="form-check-label">Publicar</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div v-if="editarMensagens" class="d-flex justify-content-end mt-3">
                <button @click="cancelarPublicarChat()" class="btn btn-danger me-2"
                    :disabled="loading">Cancelar</button>
                <button @click="publicarMensagensFAQ()" class="btn btn-success" :disabled="loading">Salvar
                    alterações</button>
            </div>
        </div>

        <!-- Placeholder quando nenhum chat está selecionado -->
        <div v-else class="col-8 d-flex align-items-center justify-content-center"
            style="background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem; height: 100vh;">
            <div class="text-center">
                <h1 v-if="this.loadingstats == 0" class="text-white">Acesse um chat para visualizá-lo.</h1>
                <div v-if="this.loadingstats == 1" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</template>
