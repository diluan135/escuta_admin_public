import axios from './axiosConfig.js';
import { createStore } from 'vuex';

export default createStore({
    state() {
        return {
            loading: true, // Adiciona a propriedade `loading`
            currentView: 'novaSolicitacao',
            user: null,
            chats: [],
            chatsAbertos: [],
            chatsFechados: [],
            enquetes: [],
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
    },
    actions: {
        async fetchData({ commit, dispatch }) {
            // Inicia o estado de carregamento
            commit('setLoading', true);

            // Dispara todas as ações de fetch em paralelo
            Promise.all([
                dispatch('fetchChats'),
                dispatch('fetchChatsAbertos'),
                dispatch('fetchChatsFechados'),
                dispatch('fetchEnquetes')
            ]);

            // Finaliza o estado de carregamento
            commit('setLoading', false);
        },
        async fetchChats({ commit }) {            
            try {
                console.log('aaaaaaaaaaa');
                
                const response = await axios.get('/api/chat');
                commit('setChats', response.data);
            } catch (error) {
                console.error('Failed to fetch chats', error);
            }
        },
        async fetchChatsAbertos({ commit }) {            
            console.log('aaaaaaaaaaa');
            
            try {
                const response = await axios.get('/api/chat/abertos');
                commit('setChatsAbertos', response.data);
            } catch (error) {
                console.error('Failed to fetch chats', error);
            }
        },
        async fetchChatsFechados({ commit }) {            
            console.log('aaaaaaaaaaa');
            try {
                const response = await axios.get('/api/chat/fechados');
                commit('setChatsFechados', response.data);
            } catch (error) {
                console.error('Failed to fetch chats', error);
            }
        },
        async fetchEnquetes({ commit }) {
            console.log('aaaaaaaaaaa');
            try {
                const response = await axios.get('/api/enquetes');
                commit('setEnquetes', response.data);
            } catch (error) {
                console.error('Failed to fetch enquetes', error);
            }
        },
    },
});
