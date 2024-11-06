import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import '../css/app.css';
import '../css/style.css';
import { createApp } from 'vue';
import App from './components/App.vue';
import store from './store';
/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'


const app = createApp(App);
app.component('font-awesome-icon', FontAwesomeIcon)
// Despacha a ação de carregar todos os dados
store.dispatch('fetchData');

app.use(store);

app.mount('#app');
