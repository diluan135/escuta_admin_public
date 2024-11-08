<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            titulo: '',
            descricao: '',
            editarId: null,
            editarTitulo: '',
            editarDescricao: '',
            isPublicado: true, // Variável para controle de publicação
        };
    },
    computed: {
        idServidor() {
            return window.idServidor;
        },
        ...mapState({
            faq: state => state.faq
        })
    },
    methods: {
        ...mapActions(['fetchFAQ']),

        async adicionarFAQ() {
            try {
                const response = await axios.post('/api/FAQ/criarFAQ', {
                    'idServidor': this.idServidor,
                    'titulo': this.titulo,
                    'descricao': this.descricao
                });
                this.titulo = '';
                this.descricao = '';
                this.fetchFAQ();
                console.log(response.data);
            } catch (error) {
                console.error('Failed to fetch FAQ', error);
            }
        },

        // Editar FAQ
        editarFAQ(id, titulo, descricao) {
            this.editarId = id;
            this.editarTitulo = titulo;
            this.editarDescricao = descricao;
        },

        // Salvar FAQ editada
        async salvarFAQ() {
            try {
                const response = await axios.put(`/api/FAQ/atualizarFAQ/${this.editarId}`, {
                    'titulo': this.editarTitulo,
                    'descricao': this.editarDescricao,
                    'publicado': this.isPublicado,
                });
                this.fetchFAQ();
                this.editarId = null;  // Limpa o campo de edição
                console.log(response.data);
            } catch (error) {
                console.error('Failed to update FAQ', error);
            }
        },

        // Publicar ou despublicar FAQ
        async togglePublicacao(faqId) {
            try {
                const resposta = await axios.put(`/api/FAQ/alterarStatus/${faqId}`, {
                    'publicado': this.isPublicado ? 1 : 0
                });
                this.fetchFAQ();
                console.log(resposta.data);
            } catch (error) {
                console.error('Failed to change FAQ status', error);
            }
        },

        // Excluir FAQ
        async excluirFAQ(faqId) {
            try {
                const resposta = await axios.delete(`/api/FAQ/excluirFAQ/${faqId}`);
                this.fetchFAQ();
                console.log(resposta.data);
            } catch (error) {
                console.error('Failed to delete FAQ', error);
            }
        }
    },
    mounted() {
        this.fetchFAQ();
    }
};
</script>

<template>
    <div style="padding-left:4%; padding-right: 4%; padding-top: 2%; overflow-y: auto; height: calc(100vh - 5rem);">
        <div class="row" style="">

            <!-- ----------------------------------- ADICIONAR FAQ --------------------------------------- -->
            <div class="row" style=" color:white; margin-bottom: 4vh;">
                <div style="margin-right:2vw; height: auto;" class="col templateBox">
                    <h2 style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">Adicionar FAQ</h2>
                    <input type="text" v-model="titulo" placeholder="Título" class="my-input">
                    <textarea v-model="descricao" placeholder="Descrição..." class="my-input"
                        style="height: 5rem; resize: none; margin-top: 10px;"></textarea>
                    <button class="btn btn-success col-1" @click="adicionarFAQ">Adicionar</button>
                </div>
            </div>

            <!-- ----------------------------------- LISTAR FAQ --------------------------------------- -->
            <div class="row" style=" color:white; margin-bottom: 4vh;">
                <div style="margin-right:2vw; height: auto;" class="col templateBox">
                    <h2 style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">FAQ's criadas</h2>
                    <div v-for="faq in faq" :key="faq.id">
                        <div class="row faq-item">
                            <h5 class="col" style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">{{
                                faq.titulo }}</h5>
                            <button class="col" @click="faq.show = !faq.show">Ver</button>
                        </div>
                        <div class="row">
                            <p class="col" v-if="faq.show"
                                style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">
                                {{ faq.descricao }}
                            </p>
                            <div class="col" v-if="faq.show">
                                <!-- Editar e Publicar/Despublicar -->
                                <button class="btn btn-warning" @click="editarFAQ(faq.id, faq.titulo, faq.descricao)">
                                    Editar
                                </button>
                                <button class="btn btn-info" @click="togglePublicacao(faq.id)">
                                    {{ faq.publicado === 1 ? 'Despublicar' : 'Publicar' }}
                                </button>
                                <button class="btn btn-danger" @click="excluirFAQ(faq.id)">Excluir</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- --------------------------- Modal de Edição --------------------------- -->
            <div v-if="editarId" class="modal">
                <div class="modal-content">
                    <h3>Editar FAQ</h3>
                    <input v-model="editarTitulo" placeholder="Título" class="my-input">
                    <textarea v-model="editarDescricao" placeholder="Descrição" class="my-input"
                        style="height: 5rem; resize: none; margin-top: 10px;"></textarea>
                    <button class="btn btn-primary" @click="salvarFAQ">Salvar</button>
                    <button class="btn btn-secondary" @click="editarId = null">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.faq-item {
    margin-bottom: 2vh;
    padding-left: 1vw;
}

.faq-item button {
    margin-right: 1vw;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
    width: 100%;
}
</style>
