# Task Management API (Symfony + Docker + JWT)

## 📌 Описание
Простое REST API для управления задачами на базе **Symfony** и **PostgreSQL**.

Функционал:
- 🔑 Аутентификация через JWT
- 📋 Получение всех задач (с пагинацией и фильтрацией)
- ➕ Создание новой задачи
- ✏️ Обновление задачи по ID
- ❌ Удаление задачи по ID
- 🔍 Получение задачи по ID

---

## 🚀 Установка и запуск

Скопируйте и выполните в терминале:
```bash
git clone https://github.com/yourusername/task_management.git
cd task_management

# Собрать и запустить контейнеры
docker compose build --no-cache && docker compose up -d

# Контейнер
docker compose exec app sh

# Применить миграции
docker compose exec php bin/console doctrine:migrations:migrate

# Очистить кеш
php bin/console cache:clear

# Запуск тесты
vendor/bin/phpunit
 
# Создать пользователя
php bin/console app:create-user test@example.com password123

# Генерация JWT ключей
mkdir -p config/jwt
php bin/console lexik:jwt:generate-keypair

# Указать фразу-пароль в .env
echo "JWT_PASSPHRASE=your_passphrase" >> .env

# В корне проекта лежит Postman коллекция
Task_management_API.postman_collection.json

# Получить токен
POST /api/login
Content-Type: application/json

{
  "email": "**",
  "password": "**"
}
