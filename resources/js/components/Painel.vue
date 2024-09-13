<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';
export default {
    data() {
        return {
            exibirEnquetes: false,
        }
    },
    computed: {
        ...mapState(['enquetes']),
        reversedEnquetes() {
            return [...this.enquetes].reverse();
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
    <div style="padding:4%;">
        <div class="row" style="height: calc(100vh - 5rem);">

            <!-- Enquetes -->
            <div class="col-3 templateBox" style="margin-right:4vw;">
                <div style="color: #fff; margin: 20px;">
                    <div class="row">
                        <div class="col">
                            <h2>Enquetes</h2>
                        </div>
                        <div class="col text-end">
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
                                        opção: {{ opcao.opcao }}
                                    </p>
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
                    <div v-else>
                        cu
                    </div>
                </div>
            </div>


            <div class="col-3 templateBox">

            </div>
        </div>
    </div>

</template>
