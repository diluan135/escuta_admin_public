<!-- src/views/Logout.vue -->
<template>
    <div>
        <!-- O Vue Router irá redirecionar após o logout -->
    </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
    setup() {
        const router = useRouter();

        // Função de logout
        const logout = async () => {
            try {
                await axios.post('/logout'); // A rota de logout do Laravel
                router.push('/'); // Redirecionar para a página inicial ou login
            } catch (error) {
                console.error('Logout failed', error);
            }
        };

        logout();
    }
};
</script>
