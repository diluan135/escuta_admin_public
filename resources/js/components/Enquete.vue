<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            titulo: '',
            descricao: '',
            opcoes: [
                { texto: '', cor: '#6adae9' },
                { texto: '', cor: '#6adae9' }
            ],
            loadingstats: 0,
            numOpcoes: 2,
            encerra_em: new Date().toISOString().slice(0, 16) // Definindo o valor inicial com o formato correto
        }
    },
    methods: {
        async criarEnquete() {
            this.loadingstats = 1;

            if (this.titulo == '' || this.titulo == null) {
                alert('O título não pode estar vazio.');
                return;
            } else {
                const opcoesValidas = this.opcoes.every(opcao => opcao.texto && opcao.texto.trim() !== '');
                if (!opcoesValidas) {
                    alert('Todas as opções devem ter texto válido.');
                    return;
                } else {
                    try {
                        // Já formatado corretamente, apenas utilizar
                        const formattedEncerraEm = this.encerra_em;

                        // Garantir que as opções tenham o formato correto
                        const opcoesFormatadas = this.opcoes.map(opcao => ({
                            texto: opcao.texto,
                            cor: opcao.cor
                        }));


                        await axios.post('/api/enquete/criarEnquete', {
                            titulo: this.titulo,
                            admin_id: parseInt(this.idServidor, 10),
                            descricao: this.descricao,
                            opcoes: opcoesFormatadas, // Enviando as opções com texto e cor
                            encerra_em: formattedEncerraEm, // Enviando a data formatada
                        });
                    } catch (error) {
                        console.error(error);
                    } finally {
                        await this.$store.dispatch('fetchEnquetes');
                        this.titulo = '';
                        this.descricao = '';
                        this.opcoes = [];
                        this.numOpcoes = 1;
                        this.encerra_em = new Date().toISOString().slice(0, 16); // Resetando para o formato correto
                        this.loadingstats = 0;
                        alert('Enquete criada com sucesso!')
                    }
                }
            }



        },
        adicionarOpcao() {
            this.opcoes.push({ texto: '', cor: '#ffffff' });
        },
        removerOpcao(index) {
            if (this.numOpcoes > 2) {
                this.opcoes.splice(index, 1);
                this.numOpcoes--;
            }
        },
    },
    computed: {
        idServidor() {
            return window.idServidor;
        }
    },
}
</script>

<template>
    <div class="mt-5 d-flex flex-column align-items-center justify-content-center">

        <div class="d-flex flex-column justify-content-center align-items-center mb-5">
            <h1 class="lemon-font text-white m-0">ENQUETES</h1>
        </div>

        <div class="row d-flex flex-column justify-start p-4"
            style="background: #141932; border-radius: 1rem; height: 100%; width: 80vw;">

            <div class="d-flex flex-column justify-start gap-3 mb-4 p-0">
                <input type="text" v-model="titulo" placeholder="Título da enquete" class="my-input w-50">
                <textarea v-model="descricao" placeholder="Descrição..." class="my-input w-100"
                    style="height: 5rem; resize: none;"></textarea>
            </div>

            <div class="row mb-2 p-0">

                <div class="col-8" style="max-height: 5rem; overflow-y: auto;">
                    <div v-for="(opcao, index) in opcoes" :key="index" class="mb-1 position-relative">
                        <div class="input-wrapper d-flex flex-row gap-3">
                            <input type="text" v-model="opcao.texto" :placeholder="`Opção #${index + 1}`"
                                class="my-input">
                            <button v-if="opcoes.length > 2" @click="removerOpcao(index)" class="remove-btn"
                                style="margin-right: 3.5rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                            <input id="color-input" type="color" v-model="opcao.cor" style="margin-top: 0.3rem;">
                        </div>
                    </div>
                </div>

                <div class="col-4 d-flex flex-column align-items-end justify-content-start">
                    <span class="text-white" style="margin-right: 0.5rem;">Encerra em:</span>
                    <input class="my-input" style="width: 55%;" type="datetime-local" v-model="encerra_em">
                </div>

            </div>

            <div class="d-flex flex-row justify-content-between">
                <button @click="adicionarOpcao" class="btn btn-link p-0">Adicionar opção</button>
                <button @click="criarEnquete" class="botao azul ms-auto" style="margin-right: 0.7rem;">{{
                    this.loadingstats ? 'Criando...' : 'Criar' }}</button>
            </div>
        </div>
    </div>
</template>
