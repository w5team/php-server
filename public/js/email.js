
window.onload = () => {

	// Ativando o combo select
	$(".ui.fluid.dropdown").dropdown({clearable: true, allowAdditions: true});



	$("#formEmail").submit(function(e){
		e.preventDefault();

		var data = {
			titulo : $("#titulo").val().trim(),
			mensagem: $("#mensagem").val().trim(),
			destino: $("#emails").val()
		}

		if(data.titulo == '') {
			alert('Digite um título para o email!');
			return $("#titulo").select();
		}

		if(data.mensagem == '') {
			alert('Qual é a mensagem?');
			return $("#mensagem").select();
		}

		if(data.destino == 0 || data.destino == null) {
			alert('Selecione o destinatário!');
			return $("#emails").select();
		}

		$.post('/email/enviar', data, (ret) => { console.log(ret)
			// if("undefined" == typeof ret['data'] || ret.data.status == 'error') {
			// 	alert('Não consegui enviar o email!')
			// } else {
			// 	$("#titulo").val('')
			// 	$("#mensagem").val('')
			// 	$("#emails").dropdown('clear')

			// 	alert('Email enviado!');
			// }
		}, 'json')
	})



}