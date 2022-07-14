<?php
$this->addExternalCss("https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css");
$this->addExternalJs("https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js");
?>

<br>
<form id="jsTestForm" class="row g-3 needs-validation" novalidate>
    <?=bitrix_sessid_post()?>
    <div class="row g-1">
        <div class="col-md-4">
            <label for="validationFIO" class="form-label">ФИО</label>
            <input type="text" class="form-control" id="validationFIO" value="Иван" required>
            <div class="valid-feedback">
                Все хорошо!
            </div>
            <div class="invalid-feedback">
                Пожалуйста, укажите корректно имя пользователя.
            </div>
        </div>
    </div>
    <div class="row g-1">
        <div class="col-md-4">
            <label for="validationEMail" class="form-label">Е-mail</label>
            <div class="input-group has-validation">
                <span class="input-group-text" id="inputGroupPrepend">@</span>
                <input type="email" class="form-control" id="validationEMail" aria-describedby="inputGroupPrepend" required>
                <div class="valid-feedback">
                    Все хорошо!
                </div>
                <div class="invalid-feedback">
                    Пожалуйста, укажите корректно Е-mail.
                </div>
            </div>
        </div>
    </div>
    <div class="row g-1">
        <div class="col-md-4">
            <label for="validationPhone" class="form-label">Телефон</label>
            <input type="text" class="form-control" id="validationPhone" value="">
        </div>
    </div>
    <div class="row g-1">
        <div class="col-md-4">
            <label for="validationQuestion" class="form-label">Вопрос</label>
            <textarea class="form-control rounded-0" id="validationQuestion" rows="5" required></textarea>
            <div class="valid-feedback">
                Все хорошо!
            </div>
            <div class="invalid-feedback">
                Пожалуйста, укажите вопрос.
            </div>
        </div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit" id="jsSendTestForm">Отправить форму</button>
    </div>
</form>
<br>
<div class="container g-1">
    <div class="row">
        <div class="col-12">

        </div>
    </div>
</div>
