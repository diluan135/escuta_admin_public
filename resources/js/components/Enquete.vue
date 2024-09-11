<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            titulo: '',
            descricao: '',
            opcoes: [],
            numOpcoes: 2,
            encerra_em: new Date().toISOString().slice(0, 16) // Definindo o valor inicial com o formato correto
        }
    },
    methods: {
        async criarEnquete() {
            try {
                // Já formatado corretamente, apenas utilizar
                const formattedEncerraEm = this.encerra_em;

                // Davi, acho que é interessante colocar um loading quando iniciar essa função porque ela está demorando pra ser finalizada, e ao terminar tirar o estado de loading, você pode criar uma variável no data como loading e deixar como falso, e iniciar o loading na hora que o botão for clicado iniciando uma animaçãozinha de loading

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
                console.log('Enquete criada com sucesso!');

                // davi, quando chegar aqui você só desativa a variável de loading
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
    <div class="container mt-4">

        <div>
            <h1 class="lemon-font text-white" style="margin-left: 4rem;">ENQUETES</h1>
            <div style="margin-left: 1rem;">
                <p class="text-white m-0">Esta seção é utilizada para criar enquetes, que serão enviadas aos usuários
                </p>
                <p class="text-white">do transporte público, possibilitando-os a votar no que os favorecem!</p>
            </div>
        </div>

        <div class="row d-flex flex-column justify-start w-75 p-4"
            style="background: #141932; border-radius: 1rem; height: 100%;">

            <div class="d-flex flex-column justify-start gap-3 mb-4 p-0">
                <input type="text" v-model="titulo" placeholder="Título da enquete" class="my-input w-50">
                <textarea v-model="descricao" placeholder="Descrição..." class="my-input w-100"
                    style="height: 5rem; resize: none;"></textarea>
            </div>

            <div class="row mb-2 p-0">

                <div class="col-8" style="max-height: 5rem; overflow-y: auto;">
                    <div v-for="(opcao, index) in numOpcoes" :key="index" class="mb-1 position-relative">
                        <div class="input-wrapper d-flex flex-row gap-3">
                            <input type="text" v-model="opcoes[index]" :placeholder="`Opção #${index + 1}`"
                                class="my-input white">        
                            <button v-if="numOpcoes > 2" @click="removerOpcao(index)" class="remove-btn"
                                style="margin-right: 3.5rem;">
                                <i class="fas fa-trash"></i>                               
                            </button>
                            <input id="color-input" type="color" v-model="opcao.cor" style="margin-top: 0.3rem;">
                        </div>                        
                    </div>
                </div>

                <div class="col-4 d-flex flex-column align-items-end justify-content-around ">
                    <span class="text-white" style="margin-right: 0.5rem;">Encerra em:</span>
                    <input class="my-input w-75" type="datetime-local" v-model="encerra_em">
                </div>

            </div>

            <div class="d-flex flex-row justify-content-between">
                <button @click="numOpcoes++" class="btn btn-link p-0">Adicionar opção</button>
                <button @click="criarEnquete" class="botao azul ms-auto">Enviar</button>
            </div>
        </div>
    </div>
</template>
