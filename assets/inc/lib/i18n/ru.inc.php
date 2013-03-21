<?
	// `Русская` локализация
	
	I18n::Set("byte.size.grades", array("н/д", "б", "Кб", "Мб", "Гб", "Тб", "Пб", "Эб", "Зб", "Йб") );

	I18n::Set("security.Meister.description", "Встроенный пользователь для обслуживания системы");
	I18n::Set("security.site_lockdown.title", "Сайт заблокирован администратором");
	I18n::Set("security.site_lockdown.button_title", "Я знаю специальное слово. Покажите мне сайт, пожалуйста!");

	I18n::Set("manage.interface.filemanager.title", "Менеджер файлов");

	/* Системные настройки */
	I18n::Set("system.settings_types.text", "Текст");
	I18n::Set("system.settings_types.number", "Число");
	I18n::Set("system.settings_types.boolean", "Галочка");
	I18n::Set("system.settings_types.password", "Пароль");
	I18n::Set("system.settings_types.big_text", "Многострочный текст");

	I18n::Set("system.settings_sections._system", "Системные");

	I18n::Set("system.settings.site_lock.title", "Блокировка сайта");
	I18n::Set("system.settings.site_lock.description", "Эта настройка используется для блокировки сайта от нежелательных посетителей (например, во время разработки)");
	
	I18n::Set("system.settings.site_lock_password.title", "Кодовое слово авторизации для разблокировки сайта");
	I18n::Set("system.settings.site_lock_password.description", "Эта настройка используется для указания кодового слова, введя которое человек даже при заблокированном сайте сможет его увидеть");

	I18n::Set("system.settings.password_recovery_email.title", "Электронная почта человека, который может восстановить пароль пользователя");
	I18n::Set("system.settings.password_recovery_email.description", "С помощью этой настройки вы можете указать доступный всем адрес электронной почты, показываемый на вкладке восстановления пароля страницы входа в систему");

	I18n::Set("system.settings.password_recovery_icq.title", "Номер ICQ человека, который может восстановить пароль пользователя");
	I18n::Set("system.settings.password_recovery_icq.description", "С помощью этой настройки вы можете указать доступный всем номер ICQ, показываемый на вкладке восстановления пароля страницы входа в систему");

	I18n::Set("system.settings.password_recovery_xmpp.title", "XMPP UIN человека, который может восстановить пароль пользователя");
	I18n::Set("system.settings.password_recovery_xmpp.description", "С помощью этой настройки вы можете указать доступный всем уникальный идентификационный номер XMPP-совместимого мессенджера, показываемый на вкладке восстановления пароля страницы входа в систему");

	I18n::Set("system.settings.password_recovery_phone.title", "Телефон человека, который может восстановить пароль пользователя");
	I18n::Set("system.settings.password_recovery_phone.description", "С помощью этой настройки вы можете укахать самый олдскульный реквизит для связи — телефон");
?>
