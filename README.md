# Real-Time Chat System / Sistema de Chat em Tempo Real

This is a project developed using the **Laravel** framework on the backend and **Vue.js** on the frontend. It uses several libraries, including Vue Router, Vite, Vuex, Laravel Echo, and Axios to provide a real-time chat experience, user management, and ticket sales for events.

Este é um projeto desenvolvido utilizando o framework **Laravel** no backend e **Vue.js** no frontend. Ele faz uso de várias bibliotecas, incluindo Vue Router, Vite, Vuex, Laravel Echo, e Axios para fornecer uma experiência de chat em tempo real, gerenciamento de usuários e vendas de ingressos para eventos.

## Technologies Used / Tecnologias Utilizadas

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js, Vite, Vue Router, Vuex
- **Real-Time Chat**: Laravel Echo, Pusher
- **Database**: MySQL
- **Notifications**: Laravel Notifications (for received messages notification)
- **Authentication and Security**: Laravel Passport (for API authentication), Password Hashing
- **Email Sending**: Laravel Mail (for account recovery and creation via email)

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js, Vite, Vue Router, Vuex
- **Chat em Tempo Real**: Laravel Echo, Pusher
- **Banco de Dados**: MySQL
- **Notificações**: Laravel Notifications (para notificação de mensagens recebidas)
- **Autenticação e Segurança**: Laravel Passport (para autenticação via API), Hash de Senhas
- **Envio de E-mails**: Laravel Mail (para recuperação de conta e criação de conta via e-mail)

## Features / Funcionalidades

### 1. **Account Management / Gerenciamento de Conta**
- Account creation via email.
- Account recovery with email sending.
- Passwords stored securely (hashed).

### 1. **Gerenciamento de Conta**
- Sistema de criação de conta via e-mail.
- Recuperação de conta com envio de e-mail.
- Senhas armazenadas de forma segura (hash).

### 2. **Real-Time Chat / Chat em Tempo Real**
- Communication between users and administrators in real time using **Laravel Echo** and **Pusher**.
- Notification of new received messages.

### 2. **Chat em Tempo Real**
- Comunicação entre usuário e administrador em tempo real utilizando **Laravel Echo** e **Pusher**.
- Notificação de novas mensagens recebidas.

### 3. **Poll and FAQ Creation / Criação de Enquetes e FAQs**
- Administrators can create polls and FAQs for user interaction.

### 3. **Criação de Enquetes e FAQs**
- Os administradores podem criar enquetes e FAQs para interação com os usuários.

### 4. **Voting System / Sistema de Votação**
- Users can vote in polls created by administrators.

### 4. **Sistema de Votação**
- Usuários podem votar em enquetes criadas pelos administradores.

### 5. **Control Dashboard / Dashboard de Controle**
- Administrators have access to a BI with information from various request types.

### 5. **Dashboard de controle**
- Administradores têm acesso a um BI com informações de diferentes tipos de solicitações.

## Installation / Instalação

### Backend (Laravel) / Backend (Laravel)

1. Clone the repository / Clone o repositório:

   ```bash
   git clone https://github.com/diluan135/escuta_admin_public.git
   composer install

2. Access the cloned repository / Acesse o repositório onde foi clonado

3. Install the dependencies / Instale as dependências:
    composer install

4. Configure the .env file with your database, Pusher, and email settings / Configure o .env corretamente com seu BD, PUSHER e EMAIL

5. Generate the application key and run the migrations / Gere a chave da aplicação e rode as migrações:

    php artisan key:generate
    php artisan migrate
   
6. Start the server / Inicie o servidor:

    php artisan serve

7. Install the frontend dependencies / Instale as dependências frontend:

   npm install

8. Compile the files / Compile os arquivos:

   npm run dev
