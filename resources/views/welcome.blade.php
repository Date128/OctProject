<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Управление пользователями</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Создать пользователя</h2>
            <form id="createUserForm" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-gray-700">Имя:</label>
                    <input type="text" id="name" name="name" required
                           class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" required
                           class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="password" class="block text-gray-700">Пароль:</label>
                    <input type="password" id="password" name="password" required
                           class="w-full p-2 border rounded">
                </div>
                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Создать
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Поиск пользователя</h2>
            <form id="searchForm" class="space-y-4">
                <div>
                    <label for="searchId" class="block text-gray-700">ID пользователя:</label>
                    <input type="number" id="searchId" name="searchId"
                           class="w-full p-2 border rounded" placeholder="Введите ID">
                </div>
                <button type="submit"
                        class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                    Найти
                </button>
            </form>
            <div id="searchResult" class="mt-4"></div>
        </div>
    </div>


    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Редактировать пользователя</h2>
            <form id="editUserForm" class="space-y-4">
                @csrf
                <input type="hidden" id="editUserId" name="id">
                <div>
                    <label for="editName" class="block text-gray-700">Имя:</label>
                    <input type="text" id="editName" name="name" required
                           class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="editEmail" class="block text-gray-700">Email:</label>
                    <input type="email" id="editEmail" name="email" required
                           class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="editPassword" class="block text-gray-700">Новый пароль:</label>
                    <input type="password" id="editPassword" name="password"
                           class="w-full p-2 border rounded"
                           placeholder="Оставьте пустым чтобы не менять">
                    <p class="text-sm text-gray-500 mt-1">Минимум 8 символов</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Отмена
                    </button>
                    <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md mt-8">
        <h2 class="text-xl font-bold mb-4">Список пользователей</h2>
        <div class="mb-4 flex space-x-4">
            <button onclick="loadUsers()"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Обновить список
            </button>
        </div>
        <div id="usersList"></div>
    </div>
</div>

<script>
    document.getElementById('createUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: formData.get('password')
        };

        try {
            const response = await fetch('/api/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert('Пользователь создан!');
                this.reset();
                loadUsers();
            } else {
                if (result.errors && result.errors.email) {
                    alert('Ошибка: ' + result.errors.email[0]);
                } else {
                    alert('Ошибка при создании пользователя: ' + (result.message || 'Неизвестная ошибка'));
                }
            }
        } catch (error) {
            alert('Ошибка: ' + error.message);
        }
    });

    document.getElementById('searchForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const userId = document.getElementById('searchId').value;

        if (!userId) {
            alert('Введите ID пользователя');
            return;
        }

        try {
            const response = await fetch(`/api/users/${userId}`);
            const result = await response.json();

            const searchResult = document.getElementById('searchResult');


            if (response.ok) {
                searchResult.innerHTML = `
                    <div class="bg-green-50 p-4 rounded border border-green-200">
                        <h3 class="font-bold text-green-800">Найден пользователь:</h3>
                        <p><strong>ID:</strong> ${result.id}</p>
                        <p><strong>Имя:</strong> ${result.name}</p>
                        <p><strong>Email:</strong> ${result.email}</p>
                        <div class="mt-2">
                            <button onclick="openEditModal(${result.id}, '${result.name.replace(/'/g, "\\'")}', '${result.email.replace(/'/g, "\\'")}')"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded text-sm mr-2">
                                Редактировать
                            </button>
                            <button onclick="deleteUser(${result.id})"
                                    class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                                Удалить
                            </button>
                        </div>
                    </div>
                `;
            } else {
                searchResult.innerHTML = `
                    <div class="bg-red-50 p-4 rounded border border-red-200">
                        <p class="text-red-800">${result.error || 'Пользователь не найден'}</p>
                    </div>
                `;
            }
        } catch (error) {
            alert('Ошибка при поиске: ' + error.message);
        }
    });

    async function loadUsers() {
        try {
            const response = await fetch('/api/users');
            const users = await response.json();

            const usersList = document.getElementById('usersList');
            usersList.innerHTML = '';

            if (users.length === 0) {
                usersList.innerHTML = '<p class="text-gray-500">Пользователи не найдены</p>';
                return;
            }

            const table = `
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">ID</th>
                            <th class="border p-2">Имя</th>
                            <th class="border p-2">Email</th>
                            <th class="border p-2">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td class="border p-2">${user.id}</td>
                                <td class="border p-2">${user.name}</td>
                                <td class="border p-2">${user.email}</td>
                                <td class="border p-2">
                                    <button onclick="openEditModal(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.email.replace(/'/g, "\\'")}')"
                                            class="bg-yellow-500 text-white px-2 py-1 rounded mr-2">
                                        Редактировать
                                    </button>
                                    <button onclick="deleteUser(${user.id})"
                                            class="bg-red-500 text-white px-2 py-1 rounded">
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            usersList.innerHTML = table;
        } catch (error) {
            alert('Ошибка при загрузке пользователей: ' + error.message);
        }
    }

    function clearSearch() {
        document.getElementById('searchId').value = '';
        document.getElementById('searchResult').innerHTML = '';
        loadUsers();
    }


    function openEditModal(id, name, email) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editPassword').value = '';
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editUserForm').reset();
    }

    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const id = document.getElementById('editUserId').value;
        const data = {
            name: document.getElementById('editName').value,
            email: document.getElementById('editEmail').value
        };

        const password = document.getElementById('editPassword').value;
        if (password) {
            data.password = password;
        }

        try {
            const response = await fetch(`/api/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                alert('Пользователь обновлен!');
                closeEditModal();
                loadUsers();
                clearSearch();
            } else {
                if (result.errors && result.errors.email) {
                    alert('Ошибка: ' + result.errors.email[0]);
                } else {
                    alert('Ошибка при обновлении пользователя: ' + (result.message || 'Неизвестная ошибка'));
                }
            }
        } catch (error) {
            alert('Ошибка: ' + error.message);
        }
    });

    async function deleteUser(id) {
        if (confirm('Удалить пользователя?')) {
            try {
                const response = await fetch(`/api/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    alert('Пользователь удален!');
                    loadUsers();
                    clearSearch();
                } else {
                    const error = await response.json();
                    alert('Ошибка при удалении пользователя: ' + (error.message || 'Неизвестная ошибка'));
                }
            } catch (error) {
                alert('Ошибка: ' + error.message);
            }
        }
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    document.addEventListener('DOMContentLoaded', loadUsers);
</script>
</body>
</html>

