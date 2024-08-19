<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';
export default {
    data() {
        return {
        }
    },
    computed: {
        ...mapState(['enquetes']),
        reversedEnquetes() {
            return [...this.enquetes].reverse();
        }
    },
    mounted() {
    }
}
</script>

<template>
    <div v-for="enquete in reversedEnquetes" :key="enquete.id">
        <h1>{{ enquete.titulo }}</h1>
        <p v-for="opcao in enquete.opcoes">opção: {{ opcao.opcao }}</p>
        <p>Descrição: {{ enquete.descricao }}</p>
        <p>Encerra em: {{ enquete.encerra_em }}</p>
    </div>
</template>
