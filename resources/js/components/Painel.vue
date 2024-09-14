<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';
import BarChart from './BarChart.vue';

export default {
    components: {
        BarChart
    },
    data() {
        return {
            exibirEnquetes: false,
            exibirUsuarios: false,
        }
    },
    computed: {
        ...mapState(['enquetes', 'usuarios', 'chats', 'chatsAbertos', 'chatsFechados']),
        reversedEnquetes() {
            return [...this.enquetes].reverse();
        },
        enquetesAtivas() {
            return this.enquetes.filter(enquete => new Date(enquete.encerra_em) > new Date());
        },

        enquetesEncerradas() {
            return this.enquetes.filter(enquete => new Date(enquete.encerra_em) <= new Date());
        },
        usuariosTotais() {
            return this.usuarios.length
        },
        usuariosSemana() {
            const seteDiasAtras = new Date();
            seteDiasAtras.setDate(seteDiasAtras.getDate() - 7); // Subtrai 7 dias da data atual
            return this.usuarios.filter(usuario => new Date(usuario.created_at) >= seteDiasAtras && new Date(usuario.created_at) <= new Date());
        },
        usuariosMes() {
            const trintaUmDiasAtras = new Date();
            trintaUmDiasAtras.setDate(trintaUmDiasAtras.getDate() - 31); // Subtrai 31 dias da data atual
            return this.usuarios.filter(usuario => new Date(usuario.created_at) >= trintaUmDiasAtras && new Date(usuario.created_at) <= new Date());
        },
        chatsLinhas() {
            const contagemLinhas = {};

            // Helper function to contar as linhas
            const contarLinhas = (chatsArray) => {
                chatsArray.forEach(chat => {
                    const linha = chat.linha;
                    if (linha != null) {
                        if (contagemLinhas[linha]) {
                            contagemLinhas[linha]++;
                        } else {
                            contagemLinhas[linha] = 1;
                        }
                    }
                });
            };

            // Contar linhas dos chats
            contarLinhas(this.chats);
            contarLinhas(this.chatsAbertos);
            contarLinhas(this.chatsFechados);

            return contagemLinhas;
        },
        barChartData() {
            const labels = Object.keys(this.chatsLinhas);
            const data = Object.values(this.chatsLinhas);

            return {
                labels: labels,
                datasets: [
                    {
                        label: 'Contagem de Chats por Linha',
                        data: data,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            };
        }

    },
    mounted() {
    },
    methods: {
        toggleEnquete(id) {
            const enquete = this.reversedEnquetes.find(enquete => enquete.id === id);
            if (enquete) {
                enquete.expanded = !enquete.expanded;
            }
        },
        beforeEnter(el) {
            el.style.opacity = 0;
            el.style.maxHeight = '0';
        },
        enter(el) {
            el.style.opacity = 1;
            el.style.maxHeight = el.scrollHeight + 'px';
        },
        leave(el) {
            el.style.opacity = 0;
            el.style.maxHeight = '0';
        }
    }
}
</script>

<style scoped>
.content {
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}
</style>

<template>
    <div style="padding:4%; overflow-y: auto;">
        <div class="row" style="height: calc(100vh - 5rem);">

            <!-- Enquetes -->
            <div class="col-3 templateBox" style="margin-right:3vw;">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Enquetes</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="botaoNeon" v-if="!this.exibirEnquetes"
                                @click="exibirEnquetes = !exibirEnquetes">Exibir Enquetes</button>
                            <button class="botaoNeon" v-else @click="exibirEnquetes = !exibirEnquetes">Ocultar
                                Enquetes</button>
                        </div>


                    </div>

                    <!-- enquetes sendo exibidas -->
                    <div style="color: #fff;" v-if="this.exibirEnquetes">
                        <hr>
                        <div v-for="enquete in reversedEnquetes" :key="enquete.id">
                            <div>
                                <h2 @click="toggleEnquete(enquete.id)" style="display: inline-block; cursor: pointer;">
                                    {{ enquete.titulo }}
                                    <span
                                        :style="{ transform: enquete.expanded ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform 0.3s' }">&#9662;</span>
                                </h2>
                            </div>

                            <transition name="fade" @before-enter="beforeEnter" @enter="enter" @leave="leave">
                                <div v-show="enquete.expanded" class="content">
                                    <p>Descrição: {{ enquete.descricao }}</p>
                                    <p v-for="opcao in enquete.opcoes" :key="opcao.opcao" :style="{
                                        backgroundColor: opcao.cor ? opcao.cor : '#6adae9',
                                        width: '50%',
                                        borderRadius: '10px', /* Ajuste o valor para o arredondamento desejado */
                                        textAlign: 'center',
                                        padding: '10px', /* Adicione algum padding para espaçamento interno */
                                        margin: '5px auto' /* Centralize horizontalmente */
                                    }">
                                        {{ opcao.opcao }} | 14 votos
                                    </p>
                                    <!-- Tenho que puxar o número de votos -->
                                    <p v-if="new Date(enquete.encerra_em) > new Date()">
                                        Aberta até: {{ enquete.encerra_em }}
                                    </p>
                                    <p v-else>
                                        Encerrada em: {{ enquete.encerra_em }}
                                    </p>
                                </div>
                            </transition>

                            <hr>
                        </div>
                    </div>


                    <!-- estatísticas sobre as enquetes -->
                    <div v-else style="overflow-y: none;">
                        <hr>
                        <h3 style="font-size: 1.25rem;">Estatísticas das Enquetes</h3>
                        <p>Total de Enquetes: {{ enquetes.length }}</p>
                        <p>Enquetes Ativas: {{ enquetesAtivas.length }}</p>
                        <p>Enquetes Encerradas: {{ enquetesEncerradas.length }}</p>
                    </div>

                </div>
            </div>




            <div class="col-3 templateBox" style="margin-right:3vw;">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Chats encerrados</h2>
                        </div>
                        Colocar uns graficos bem bacanas
                        <br>colocar chats do ultimo mes, da ultima semana, do ano,
                        <br>
                    </div>
                </div>
            </div>


            <!-- --------------------------- Usuarios ---------------------------------- -->
            <div style="width: 25vw;" class="col-1 templateBox">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Usuários</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="botaoNeon" v-if="!this.exibirUsuarios"
                                @click="exibirUsuarios = !exibirUsuarios">Exibir usuarios</button>
                            <button class="botaoNeon" v-else @click="exibirUsuarios = !exibirUsuarios">Ocultar</button>
                        </div>
                        <div style="color: #fff;" v-if="this.exibirUsuarios">
                            <hr>
                            <div v-for="usuario in usuarios" style="color: #fff;" v-if="this.exibirUsuarios">
                                {{ usuario.name }} {{ usuario.sobrenome }}
                                <br>
                                Email: {{ usuario.email }}
                                <br>
                                CPF: {{ usuario.CPF }}
                                <hr>
                            </div>
                        </div>
                        <div v-else style="color: #fff;">
                            <hr>
                            <h3>Usuários totais: {{ usuariosTotais }}</h3>
                            <br>
                            <span>Novos usuários na última semana: {{ usuariosSemana.length }}</span>
                            <br>
                            <span>Novos usuários no ultimo mês: {{ usuariosMes.length }}</span>
                        </div>
                        <br>
                    </div>
                </div>
            </div>


            <!-- ----------------------------------- TABELONA GRANDE --------------------------------------- -->
            <div class="row" style="height: calc(100vh - 5rem); color:white; margin-top: 4vh;">
                <div style="width: 63vw; height: auto; margin-bottom: 20vh;"  class="col-1 templateBox">
                    <BarChart :chartData="barChartData" />
                </div>
            </div>


        </div>
    </div>

</template>
