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
            encerra_em: Date.now(+100)
        }
    },
    methods: {
        async criarEnquete() {
            try {
                const date = new Date(this.encerra_em);
                const formattedEncerraEm = date.toISOString().slice(0, 19).replace('T', ' ');

                await axios.post('/api/enquete/criarEnquete', {
                    titulo: this.titulo,
                    admin_id: parseInt(idServidor, 10),
                    descricao: this.descricao,
                    opcoes: this.opcoes,
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
                this.encerra_em = Date.now(+100)
                console.log('Enquete criada com sucesso!');
            }
        }
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

        <div v-for="(opcao, index) in numOpcoes" :key="index">
            <input type="text" v-model="opcoes[index]" :placeholder="`OPCAO ${index + 1}`">
        </div>

        <button @click="numOpcoes++">ADICIONAR OPCAO</button>
        <button @click="criarEnquete">CRIAR NOVA ENQUETE</button>
    </div>
</template>
