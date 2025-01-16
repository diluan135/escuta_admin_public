import axios from './axiosConfig.js';
import { createStore } from 'vuex';
import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configuração do Pusher e Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env, // Use para acessar variáveis de ambiente no Vite
    cluster: 'mt1',
    forceTLS: 'true',
    disableStats: true,
    enabledTransports: ['ws'], // Use WebSocket
});

const store = createStore({
    state() {
        return {
            loading: true,
            currentView: 'novaSolicitacao',
            user: null,
            chats: [],
            chatsAbertos: [],
            chatsFechados: [],
            enquetes: [],
            faq: [],
            usuarios: [],
            mensagensRecebidas: [],
        };
    },
    mutations: {
        setLoading(state, isLoading) {
            state.loading = isLoading;
        },
        setCurrentView(state, view) {
            state.currentView = view;
        },
        setChats(state, chats) {
            state.chats = chats;
        },
        setChatsAbertos(state, chatsAbertos) {
            state.chatsAbertos = chatsAbertos;
        },
        setChatsFechados(state, chatsFechados) {
            state.chatsFechados = chatsFechados;
        },
        setEnquetes(state, enquetes) {
            state.enquetes = enquetes;
        },
        setFAQ(state, faq) {
            state.faq = faq;
        },
        setUsuarios(state, usuarios) {
            state.usuarios = usuarios;
        },
        setMensagensRecebidas(state, mensagensRecebidas) {
            state.mensagensRecebidas = mensagensRecebidas;
        },
        adicionarMensagem(state, evento) {
            const chatId = evento.chat_id;
            const chat = state.chatsAbertos.find(c => c.id === chatId);

            if (chat) {
                chat.mensagens = chat.mensagens || [];
                chat.mensagens.push(evento.mensagem);

                // Adiciona a mensagem à lista de mensagens recebidas
                state.mensagensRecebidas.push(evento);
                state.mensagensRecebidas = [...state.mensagensRecebidas];
            } else {
                console.warn(`Chat com ID ${chatId} não encontrado nos chats abertos.`);
            }
        },
    },
    actions: {
        async fetchData({ commit, dispatch }) {
            commit('setLoading', true);

            await Promise.all([
                dispatch('fetchChats'),
                dispatch('fetchChatsAbertos'),
                dispatch('fetchChatsFechados'),
                dispatch('fetchEnquetes'),
                dispatch('fetchFAQ'),
                dispatch('fetchUsuarios'),
            ]);

            commit('setLoading', false);
        },
        async fetchChats({ commit }) {
            try {
                const response = await axios.get('/api/chat');
                commit('setChats', response.data);
            } catch (error) {
                console.error('Failed to fetch chats', error);
            }
        },
        async fetchChatsAbertos({ commit }) {
            try {
                const response = await axios.get('/api/chat/abertos');                
                commit('setChatsAbertos', response.data);
            } catch (error) {
                console.error('Failed to fetch chats abertos', error);
            }
        },
        async fetchChatsFechados({ commit }) {
            try {
                const response = await axios.get('/api/chat/fechados');
                commit('setChatsFechados', response.data);
            } catch (error) {
                console.error('Failed to fetch chats fechados', error);
            }
        },
        async fetchEnquetes({ commit }) {
            try {
                const response = await axios.get('/api/enquetes');
                commit('setEnquetes', response.data);
            } catch (error) {
                console.error('Failed to fetch enquetes', error);
            }
        },
        async fetchFAQ({ commit }) {
            try {
                const response = await axios.get('/api/FAQ');
                console.log(response.data);
                commit('setFAQ', response.data);
            } catch (error) {
                console.error('Failed to fetch FAQ', error);
            }
        },
        async fetchUsuarios({ commit }) {
            try {
                const response = await axios.get('/api/usuario/get');
                commit('setUsuarios', response.data);
            } catch (error) {
                console.error('Failed to fetch usuarios', error);
            }
        },
        initListener({ dispatch }) {
            window.Echo.channel('escuta')
                .listen('.escuta', (event) => {
                    dispatch('handleIncomingMessage', event.message);
                });
        },
        handleIncomingMessage({ commit, state, dispatch }, evento) {
            const chatId = evento.chat_id;
            const chat = state.chatsAbertos.find(c => c.id === chatId);

            if (chat) {
                commit('adicionarMensagem', evento);
            } else {
                console.warn(`Chat com ID ${chatId} não encontrado nos chats abertos. Recarregando chats...`);
                dispatch('fetchChats');
            }
        },
    },
});

// Inicia o listener ao criar a store
store.dispatch('initListener');

export default store;
