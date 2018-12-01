
window.onload = () => {

	// Ativando o combo select
	$(".ui.selection.dropdown").dropdown({clearable: true});



	$("#formMsg").submit(function(e){
		e.preventDefault();

		var data = {
			titulo : $("#titulo").val().trim(),
			mensagem: $("#mensagem").val().trim(),
			link: $("#link").val().trim(),
			destino: $("#destino").val()
		}

		if(data.titulo == '') {
			alert('Digite um título para a mensagem!');
			return $("#titulo").select();
		}

		if(data.mensagem == '') {
			alert('Qual é a mensagem?');
			return $("#mensagem").select();
		}

		if(data.destino == 0 || data.destino == null) {
			alert('Selecione o destinatário!');
			return $("#destino").select();
		}

		$.post('/message/create', data, (ret) => {
			if("undefined" == typeof ret['data'] || ret.data.status == 'error') {
				alert('Não consegui enviar a mensagem!')
			} else {
				$("#titulo").val('')
				$("#mensagem").val('')
				$("#link").val('')
				$(".ui.selection.dropdown").dropdown('clear')

				alert('Mensagem enviada!');
			}
		}, 'json')
	})



}