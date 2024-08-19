import axios from 'axios';

// Crie uma instância do Axios com a configuração desejada
const axiosInstance = axios.create({
    baseURL: '/', // Defina a URL base aqui
    // Você pode adicionar headers ou outras configurações aqui, se necessário
});

export default axiosInstance;
