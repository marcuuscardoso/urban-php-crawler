# Instruções para rodar a aplicação de Crawler

Este projeto utiliza o Composer e Docker para facilitar a execução do crawler. Siga as instruções abaixo para configurar e executar a aplicação.

## Pré-requisitos
- Certifique-se de ter o Docker e o Docker Compose instalados em seu sistema.

## Passos para Execução

1. **Clonar o Repositório:**
   ```bash
   git clone https://github.com/marcuuscardoso/urban-php-crawler.git
   cd urban-php-crawler
   ```

2. **Instalar Dependências:**
   ```bash
   docker-compose run --rm composer install
   ```

3. **Executar o Crawler:**
   ```bash
   docker-compose run --rm php php bin/crawler.php
   ```

   Isso iniciará o processo de crawling no site `https://amleiloeiro.com.br/` com um atraso de 500 milissegundos entre as requisições. Você poderá ver o progresso no terminal e o arquivo lotes_data.csv será criado.


4. **Personalização do Crawler:**
    - Caso necessário, você pode ajustar as configurações do crawler editando o arquivo `bin/crawler.php`.
    - Altere a URL alvo, o atraso entre as requisições, ou as regras de crawling conforme necessário.