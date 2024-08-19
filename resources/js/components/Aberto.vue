<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            loading: false,
        }

    },
    computed: {
        ...mapState(['chatsAbertos', 'user']),
        idServidor(){
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsAbertos']),
        async getMessage(chat_id) {
            const response = await axios.get('/api/mensagem', {
                params: {
                    chat_id: chat_id,
                }
            }
            )
            console.log(response)
            this.mensagens = response.data
            this.chatSelecionado = chat_id
        },
        async mandarMensagem() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post('/api/mensagem/enviarMensagem', {
                    admin_id: parseInt(idServidor, 10),
                    chat_id: this.chatSelecionado,
                    mensagem: this.novaMensagem,
                });
                console.log('Mensagem enviada:', response.data);
                this.novaMensagem = '';
                await this.getMessage(this.chatSelecionado);
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
            } finally {
                this.loading = false;
            }
        },
        async fecharChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            try {
                const response = await axios.post('/api/chat/fecharChat', {
                    chat_id: this.chatSelecionado,
                },
                );
                console.log('Chat fechado:', response.data);
                this.chatSelecionado = null;
            } catch (error) {
                console.error('Erro ao fechar chat:', error);
            }
        },
    },
    mounted() {
        this.fetchChatsAbertos();
        console.log(this.chatsAbertos);
        console.log(idServidor);
    },
};
</script>

<template>
    <h2>ABERTOOO</h2>
    <div class="d-flex">
        <div class="col-4">
            <h1>Conversas</h1>
            <div v-for="chat in chatsAbertos " :key="chat.id">
                <div>{{ chat.assunto }}</div>
                <div>{{ chat.criado_em }}</div>
                <div v-if="chat.linha != null">{{ chat.linha }}</div>
                <div>{{ chat.chat_status }}</div>
                <button @click="getMessage(chat.id)">Acessar chat</button>
                <br>
                <br>
            </div>
        </div>
        <div v-if="mensagens.length" class="col-8">
            <div class="row justify-content-end">
                <button @click="fecharChat()" class="col-2" :disabled="loading">Fechar chat</button>
            </div>
            <div v-for="mensagem in mensagens" :key="mensagem.id">
                <span>{{ mensagem }}</span>
                <span>CHAT ID: {{ mensagem.chat_id }}</span>
            </div>
            <div class="row">
                <input class="col-8" type="text" v-model="novaMensagem">
                <button @click="mandarMensagem()" class="col" :disabled="loading">Enviar mensagem</button>
            </div>
        </div>
    </div>
</template>
