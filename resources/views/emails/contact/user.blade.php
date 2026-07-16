<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <h2>Спасибо за обращение, {{ $contact->name }}!</h2>
    <p>Мы получили ваше сообщение и свяжемся с вами в ближайшее время.</p>

    <h3>Копия вашего обращения</h3>
    <p style="white-space: pre-wrap;">{{ $contact->comment }}</p>

    <hr>
    <p style="color: #888; font-size: 12px;">
        Это автоматическое письмо, отвечать на него не нужно.
    </p>
</body>
</html>
