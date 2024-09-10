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
            this.getMensagensFAQ(this.chatSelecionado);
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
                <div v-if="chat.publicado == 1">Publicado</div>
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
                <div class="row" v-if="!editarTituloAtivo">
                    <h2 class="col">{{ chatAssunto }}</h2>
                    <button class="col-2" @click="editarTitulo">Editar título</button>
                    <button @click="modoPublicarChat()" class="col-2" :disabled="loading">Editar chat</button>
                </div>
                <div v-else>
                    <input v-model="novoTitulo" class="form-control col" placeholder="Digite o novo título">
                    <button class="col-2" @click="salvarTitulo">Salvar</button>
                    <button class="col-2" @click="cancelarEdicaoTitulo">Cancelar</button>
                </div>

            </div>
            <div class="row">
                <div class="col-8">
                    <span v-if="!editarMensagens">
                        <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id">
                            <div v-if="mensagem.admin_id" class="row">
                                <span class="col">Admin {{ mensagem.admin_id }}: {{ mensagem.mensagem }}</span>
                                <span class="col">Publicado? {{ mensagem.publicado }}</span>
                            </div>
                            <div v-else class="row">
                                <span class="col" >Usuário: {{ mensagem.mensagem }}</span>
                                <span class="col">Publicado? {{ mensagem.publicado }}</span>
                            </div>
                        </div>
                    </span>
                    <div v-else>
                        <label class="row">
                            Chat publicado?
                            <input type="checkbox" :checked="this.chatPublicado == 1"
                                @change="this.chatPublicadoTemp = !this.chatPublicadoTemp" />
                        </label>

                        <div v-for="(mensagem, index) in mensagensFAQ" :key="mensagem.id">
                            <input v-model="mensagem.mensagem" class="form-control" />
                            <label>
                                Publicar
                                <input type="checkbox" :checked="mensagem.publicado == 1" v-model="publicarStatus[index]" checked />
                            </label>
                        </div>
                    </div>
                    <div v-if="editarMensagens" class="row">
                        <div class="col text-end">
                            <button @click="cancelarPublicarChat()" class="col-2" :disabled="loading">Cancelar</button>
                            <button @click="publicarMensagensFAQ()" class="col-2" :disabled="loading">Salvar
                                alterações</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
