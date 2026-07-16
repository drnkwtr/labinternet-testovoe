<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <h2>Новое обращение с сайта</h2>
    <p>Поступило новое обращение через форму обратной связи.</p>

    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Имя:</strong></td><td>{{ $contact->name }}</td></tr>
        <tr><td><strong>Телефон:</strong></td><td>{{ $contact->phone }}</td></tr>
        <tr><td><strong>Email:</strong></td><td>{{ $contact->email }}</td></tr>
        <tr><td><strong>Дата:</strong></td><td>{{ $contact->createdAt->format('d.m.Y H:i') }}</td></tr>
    </table>

    <h3>Комментарий</h3>
    <p style="white-space: pre-wrap;">{{ $contact->comment }}</p>
</body>
</html>
