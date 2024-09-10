<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            titulo: '',
            descricao: '',
            opcoes: [],
            numOpcoes: 1,
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
            if (this.opcoes.length > 1) {
                this.opcoes.splice(index, 1);
            } else {
                alert('Pelo menos uma opção deve ser mantida.');
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
    <div>
        <input type="text" v-model="titulo" placeholder="TITULO">
        <input type="text" v-model="descricao" placeholder="DESCRICAO">
        <span>Encerra em:</span>
        <input type="datetime-local" v-model="encerra_em">

        <div>
            <div v-for="(opcao, index) in opcoes" :key="index">
                <input type="text" v-model="opcao.texto" :placeholder="`OPCAO ${index + 1}`">
                <input type="color" v-model="opcao.cor">

                <button @click="removerOpcao(index)">REMOVER OPCAO</button>
            </div>

            <button @click="adicionarOpcao">ADICIONAR OPCAO</button>
            <button @click="criarEnquete">CRIAR NOVA ENQUETE</button>
        </div>
    </div>
</template>
