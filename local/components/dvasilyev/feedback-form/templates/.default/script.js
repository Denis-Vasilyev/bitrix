setTimeout(() => {

    (function () {
        document.getElementById("jsTestForm").onsubmit = function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (this.checkValidity()) {
                let data = {
                    formData: {
                        FIO: this.elements.validationFIO.value,
                        EMAIL: this.elements.validationEMail.value,
                        PHONE: this.elements.validationPhone.value,
                        QUESTION: this.elements.validationQuestion.value,
                        SESSID: document.querySelector('#' + this.id + ' > input[name=sessid]').value
                    }
                }
                console.log(data);
                this.classList.add('was-validated');
                BX.ajax.runComponentAction(
                    'dvasilyev:feedback-form',
                    'sendForm', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: data, // ключи объекта data соответствуют параметрам метода
                    })
                    .then(function(response) {
                        if (response.status === 'success') {
                            // Если форма успешно отправилась
                            if(response.data !== true) {
                                alert(response.data);
                            }

                            setTimeout(() => {
                                (
                                    function () {
                                    window.location.reload();
                                    }
                                )()
                            }, 1500);
                        }
                    });
                return false;
            }
            this.classList.add('was-validated'); //рендерит результат валидации
        }
    })();
}, 500);
