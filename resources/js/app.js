import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import App from './components/App.vue';
import store from './store';

const app = createApp(App);

// Despacha a ação de carregar todos os dados
store.dispatch('fetchData');

app.use(store);

app.mount('#app');
