<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';
import BarChart from './BarChart.vue';
import PieChart from './PieChart.vue';

export default {
    components: {
        BarChart, PieChart
    },
    data() {
        return {
            exibirEnquetes: false,
            exibirUsuarios: false,
            periodoAtual: 'Últimos 7 dias',
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
        contagensTipos() {
            const contagemTipos = {};

            // Função para normalizar os tipos
            const normalizarTipo = (tipo) => {
                if (tipo) {
                    tipo = tipo.toLowerCase().trim(); // Normaliza para minúsculas e remove espaços
                    if (['horários', 'horario'].includes(tipo)) {
                        return 'Horários';
                    }
                    if (['sugestão'].includes(tipo)) {
                        return 'Sugestão';
                    }
                    if (['reclamação'].includes(tipo)) {
                        return 'Reclamação';
                    }
                    else {
                        return 'Outros';
                    }
                }
                return 'Outros';
            };

            // Função para filtrar chats com base no período
            const filtrarPorPeriodo = (chatsArray) => {
                const agora = new Date();
                return chatsArray.filter(chat => {
                    const dataChat = new Date(chat.criado_em);
                    switch (this.periodoAtual) {
                        case 'Últimos 7 dias':
                            return (agora - dataChat) <= (7 * 24 * 60 * 60 * 1000);
                        case 'Último mês':
                            return (agora - dataChat) <= (30 * 24 * 60 * 60 * 1000);
                        case 'Última semana':
                            return (agora - dataChat) <= (7 * 24 * 60 * 60 * 1000);
                        case 'Último ano':
                            return (agora - dataChat) <= (365 * 24 * 60 * 60 * 1000);
                        case 'Último dia':
                            return (agora - dataChat) <= (24 * 60 * 60 * 1000);
                        default:
                            return true;
                    }
                });
            };

            // Helper function to contar os tipos
            const contarTipos = (chatsArray) => {
                const chatsFiltrados = filtrarPorPeriodo(chatsArray);
                chatsFiltrados.forEach(chat => {
                    const tipo = normalizarTipo(chat.tipo);
                    if (tipo) {
                        if (contagemTipos[tipo]) {
                            contagemTipos[tipo]++;
                        } else {
                            contagemTipos[tipo] = 1;
                        }
                    }
                });
            };

            // Contar tipos dos chats
            contarTipos(this.chats);
            contarTipos(this.chatsAbertos);
            contarTipos(this.chatsFechados);

            return contagemTipos;
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
        },
        pieChartData() {
            const chatsCount = this.chats.length;
            const chatsAbertosCount = this.chatsAbertos.length;
            const chatsFechadosCount = this.chatsFechados.length;

            return {
                labels: ['Chats Novos', 'Chats Abertos', 'Chats Fechados'],
                datasets: [
                    {
                        label: 'Contagem de Chats',
                        data: [chatsCount, chatsAbertosCount, chatsFechadosCount],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)', // Cor para 'Chats Novos'
                            'rgba(54, 162, 235, 0.2)',  // Cor para 'Chats Abertos'
                            'rgba(255, 206, 86, 0.2)'   // Cor para 'Chats Fechados'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',    // Borda para 'Chats Novos'
                            'rgba(54, 162, 235, 1)',    // Borda para 'Chats Abertos'
                            'rgba(255, 206, 86, 1)'     // Borda para 'Chats Fechados'
                        ],
                        borderWidth: 1
                    }
                ]
            };
        },
        legendData() {
            return {
                'Chats Novos': { count: this.chats.length, color: 'rgba(255, 99, 132, 0.2)' },
                'Chats Abertos': { count: this.chatsAbertos.length, color: 'rgba(54, 162, 235, 0.2)' },
                'Chats Fechados': { count: this.chatsFechados.length, color: 'rgba(255, 206, 86, 0.2)' }
            };
        }
    }


    ,
    mounted() {
    },
    methods: {
        alternarPeriodo() {
            const periodos = ['Últimos 7 dias', 'Último mês', 'Último ano', 'Último dia'];
            const indexAtual = periodos.indexOf(this.periodoAtual);
            const proximoIndex = (indexAtual + 1) % periodos.length;
            this.periodoAtual = periodos[proximoIndex];
        },
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
    <div style="padding-left:4%; padding-right: 4%; padding-top: 2%; overflow-y: auto; height: calc(100vh - 5rem);">
        <div class="row" style="">

            <!-- ----------------------------------- TABELONA GRANDE --------------------------------------- -->
            <div class="row" style=" color:white; margin-bottom: 4vh;">
                <div style="margin-right:2vw; width: 63vw; height: auto;" class="col-1 templateBox">
                    <h2 style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">Linhas com solicitações</h2>
                    <BarChart :chartData="barChartData" />
                </div>


                <!-- ----------------------------------- GRAFICO --------------------------------------- -->
                <div style="height: auto; margin-left: 2vh;" class="col templateBox">
                    <h2 style="padding-left:1vw; padding-top: 2vh; margin-bottom: 2vh;">Chats</h2>
                    <PieChart :chartData="pieChartData" :legendData="legendData" />
                </div>
            </div>

            <!-- Enquetes -->
            <div class="col-4 templateBox mb-4" style="margin-right:3vw;">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Enquetes</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="botao azul" style="box-shadow: none; width: auto; font-size: 14px;"
                                v-if="!this.exibirEnquetes" @click="exibirEnquetes = !exibirEnquetes">Exibir
                                Enquetes</button>
                            <button class="botao azul" style="box-shadow: none; width: auto; font-size: 14px;" v-else
                                @click="exibirEnquetes = !exibirEnquetes">Ocultar
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



            <!-- --------------------------- Chats ---------------------------------- -->
            <div class="col-4 templateBox" style="margin-right: 3vw;">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Assuntos</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="botao azul" style="box-shadow: none; width: auto; font-size: 14px;"
                                @click="alternarPeriodo">{{ periodoAtual }} <i style="margin-left:4px; rotate: 90deg;"class="fa-solid fa-arrow-right-arrow-left"></i>
                            </button>
                        </div>
                    </div>
                    <div style="color: #fff;">
                        <hr>
                        <div v-for="(contagem, tipo) in contagensTipos" :key="tipo" style="color: #fff;">
                            {{ tipo }} : {{ contagem }}
                        </div>
                    </div>
                    <br>
                </div>
            </div>

            <!-- --------------------------- Usuarios ---------------------------------- -->
            <div style="width: 25vw;" class="col-4 templateBox">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Usuários</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <button class="botao azul" style="box-shadow: none; width: auto; font-size: 14px;"
                                v-if="!this.exibirUsuarios" @click="exibirUsuarios = !exibirUsuarios">Exibir
                                usuarios</button>
                            <button class="botao azul" style="box-shadow: none; width: auto; font-size: 14px;" v-else
                                @click="exibirUsuarios = !exibirUsuarios">Ocultar</button>
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



        </div>
    </div>

</template>
