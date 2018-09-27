<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    
</head>
<style type="text/css">
		
		.body-width {
			width: 50%;
		}
		div.top-bar {
			height: 2px;
		}
		div.blue-bar {
			height: 20px;
			background: #134794;
		}
		div.title-bar {
			height: 140px;
			background-size: cover;
			padding: 20px 60px 20px 40px;
		}
		div.title {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			height: 18px;
			font-size: 15px;
			color: black;
		}
		table{
			width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 10px;
		}
		span.note{
			font-size:10px;
			font-family: 'Roboto', sans-serif;
		}
		td.firstcolumninfo{
			width: 48%;
		}
		td.secundcolumninfo{
			width: 18%;
		}
		td.firstcolumnlabel{
			width: 16%;
		}
		td.secundcolumnlabel{
			width: 18%;
		}
		table.tablapago{
			width: 100%;
			border-collapse: collapse;
		}
		thead.pago{
			background: #134794;
		}
		td.columna{
			width: 25%;
			text-align: center;
			color: #ffffff;
		}
		tr.primeracolumna{
			width: 50%;
			height: 15px;
			border: 1px solid black;
		}
		td.columnapago{
			height: 14px;
			border: 1px solid black;
			font-size: 10px;
			text-align: center;	
		}
		td.rowfirm{
			height: 80px;
			border-bottom: 1px solid black;
		}
		td.fingerprint{
			height: 80px;
			border: 2px solid black;
		}
		td.rowfirmlabel{
			font-size: 10px;
			text-align: center;	
		}

	</style>
	<body class="body-width">	
		<div class="title">BOLETA DE CONTROL DE PAGO</div>
		<table>
			<tr>
				<td class="firstcolumnlabel">Nombre Cliente:</td>
				<td class="firstcolumninfo">
					<span><strong></strong></span>
				</td>
				<td class="secundcolumnlabel">DPI:</td>
				<td class="secundcolumninfo">
					<span><strong></strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Dirección:</td>
				<td class="firstcolumninfo">
					<span><strong></strong></span>
				</td>
				<td class="secundcolumnlabel">Teléfono:</td>
				<td class="secundcolumninfo">
					<span><strong></strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Plan:</td>
				<td class="firstcolumninfo">
					<span><strong></strong></span>
				</td>
				<td class="secundcolumnlabel">Fecha de entrega:</td>
				<td class="secundcolumninfo">
					<span><strong></strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Monto:</td>
				<td class="firstcolumninfo">
					<span><strong></strong></span>
				</td>
				<td class="secundcolumnlabel">Cuota diaria:</td>
				<td class="secundcolumninfo">
					<span><strong></strong></span>
				</td>
			</tr>
		
		</table>
		<br>

		<div class="col-md-6">
		<table class="table">
			<thead>
				<tr>
					<td>No.</td>
					<td>Monto</td>
					<td>Fecha pago</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>	
			</tbody>
		</table>
		</div>

		<div class="col-md-6">
		<table class="table">
			<thead>
				<tr>
					<td>No.</td>
					<td>Monto</td>
					<td>Fecha pago</td>
				</tr>
			</thead>
			<tbody>
				<tr>

						<td></td>
						<td></td>
						<td></td>
					
						<td></td>
						<td></td>
						<td></td>
					
				</tr>

			</tbody>
		</table>
		</div>
		<div id="contain3">
			<span class="note"><strong>Nota: </strong>SE COBRARÁ MORA POR DÍA ATRASADO</span>
			<br>
			<br>
			<table>
				<tr>
					<td class="rowfirm"></td>
					<td class="rowfirm"></td>
					<td class="rowfirm"></td>
					<td class="fingerprint"></td>
				</tr>
				<tr>
					<td class="rowfirmlabel">F. Préstamos</td>
					<td class="rowfirmlabel">F. Encargada de grupo</td>
					<td class="rowfirmlabel">F. Cliente</td>
					<td class="rowfirmlabel">Huella</td>
				</tr>
			
			</table>
		</div>

	</body>
