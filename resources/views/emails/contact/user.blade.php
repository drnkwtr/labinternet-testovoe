<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #222;">
    <h2>Спасибо за обращение, {{ $contact->name }}!</h2>
    <p>Мы получили ваше сообщение и свяжемся с вами в ближайшее время.</p>

    <h3>Копия вашего обращения</h3>
    <p style="white-space: pre-wrap;">{{ $contact->comment }}</p>

    <blockquote style="margin: 20px 0; padding: 12px 16px; border-left: 4px solid #4f46e5; background: #f5f5ff; font-style: italic;">
        {{ $quote->text }}
    </blockquote>

    <hr>
    <p style="color: #888; font-size: 12px;">
        Это автоматическое письмо, отвечать на него не нужно.
    </p>
</body>
</html>
