	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
	<style type="text/css">
		#contain1{
			float: left;
			width: 49.5%;
		}
		#contain2{
			float: right;
			width: 49.5%;
		}
		#contain3{
			float: left;
			width: 50%;
		}
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
			width:100%;
			font-family: 'Roboto', sans-serif;
			font-size: 10px;
		}

		div.tablewidth{
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
		table.tablepayleft{
			border-collapse: collapse;
			float: left;
		}
		table.tablepayright{
			border-collapse: collapse;
			float: right;
		}
		thead.pago{
			background: #134794;
			border: 1px solid black;
		}
		td.columna{
			width: 25%;
			text-align: center;
			color: #ffffff;
		}
		tr.primeracolumna{
			height: 15px;
		}
		td.firscolumnpay{
			height: 14px;
			font-size: 10px;
			text-align: center;
			float: left;
			
		}

		td.secondcolumnpay{
			height: 14px;
			font-size: 10px;
			text-align: center;
			float: right;
			background: #134222;
			
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
					<span><strong>{!! $data->cliente->nombre.' '.$data->cliente->apellido !!}</strong></span>
				</td>
				<td class="secundcolumnlabel">DPI:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->cliente->dpi !!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Dirección:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->cliente->direccion!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Teléfono:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->cliente->telefono!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Plan:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->planes->descripcion!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Fecha de entrega:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->fecha_inicio!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Monto:</td>
				<td class="firstcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->montos->monto, 2, '.', '')!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Cuota diaria:</td>
				<td class="secundcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->cuota_diaria, 2, '.', '')!!}</strong></span>
				</td>
			</tr>
		</table>
		<br>
		<?php 

			$totaldias = (strtotime($data->fecha_inicio)-strtotime($data->fecha_fin))/86400;
			$totaldias = abs($totaldias); 
			$totaldias = floor($totaldias + 1);		

			$dias = intval($totaldias / 2);
			$residuo = ($totaldias % 2);
			
			$date = new \DateTime($data->fecha_inicio);
			$segundos = 0;
			$timestamp = $date->getTimestamp();
			$cant1 = 0;
			$cant2 = $dias+$residuo;
			$cant3 = 0;
			$count1 = 0;
			$count2 = $dias+$residuo;
			
				
		?>
		<div id="contain1">
		<table class="tablepayleft">
			<thead class="pago">
				<tr>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
				</tr>
			</thead>
			<tbody>
				
				@for ($i = 0; $i < $dias; $i++)
				<?php
					$segundos = $segundos + 86400;  
					$caduca = date("D", $timestamp+$segundos);  

					if ($caduca == "Sun")  {  
						$i--;
					} else {

						$fecha1 = strtotime ( '+'.$cant1.' day' , strtotime ( $data->fecha_inicio) ) ;
						$fecha1 = date ( 'd-m-Y' , $fecha1 );
						$date1 = new \DateTime($fecha1);
						$sunday1 = date("D", $date1->getTimestamp());
				?>
						<tr class="primeracolumna">
							@if($sunday1 != "Sun")
								<?php $count1++; ?>
								<td class="firscolumnpay">{!! $count1 !!}</td>
								<td class="firscolumnpay">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count1-1))), 2, '.', '')!!}</td>
								<td class="firscolumnpay">{!! $fecha1 !!}</td>
							@else
								<td class="firscolumnpay"></td>
								<td class="firscolumnpay"></td>
								<td class="firscolumnpay"></td>
							@endif
						</tr>
					<?php
						$cant1 = $i+1;
					}; ?>
				@endfor
				@if ($residuo>0)
				<?php 
					$cant1 = $dias;
					$count1++;
				
					$fecha3 = strtotime ( '+'.$cant1.' day' , strtotime ( $data->fecha_inicio ) );
					$fecha3 = date ( 'd-m-Y' , $fecha3 );
				?>
					<tr class="primeracolumna">
						<td class="firscolumnpay">{!! $count1 !!}</td>
						<td class="firscolumnpay">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count1-1))), 2, '.', '')!!}</td>
						<td class="firscolumnpay">{!! $fecha3 !!}</td>
					</tr>
				@endif

			</tbody>
		</table>
		</div>

		<div id="contain2">
		<table class="tablepayright">
			<thead class="pago">
				<tr>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
				</tr>
			</thead>
			<tbody>
			<?php
				$cant2 = $cant1;
				$count2 = $count1;
			?>
			@for ($i = 0; $i < $dias; $i++)
				<?php
					$segundos = $segundos + 86400;  
					$caduca = date("D", $timestamp+$segundos);  

					if ($caduca == "Sun")  {  
						$i--;
					} else {

						$fecha1 = strtotime ( '+'.$cant2.' day' , strtotime ( $data->fecha_inicio) ) ;
						$fecha1 = date ( 'd-m-Y' , $fecha1 );
						$date1 = new \DateTime($fecha1);
						$sunday1 = date("D", $date1->getTimestamp());
				?>
						<tr class="primeracolumna">
							@if($sunday1 != "Sun")
								<?php $count2++; ?>
								<td class="secondcolumnpay">{!! $count2 !!}</td>
								<td class="secondcolumnpay">Q. {!!number_format((float)($data->deudatotal-($data->cuota_diaria * ($count2-1))), 2, '.', '')!!}</td>
								<td class="secondcolumnpay">{!! $fecha1 !!}</td>
							@else
								<td class="secondcolumnpay"></td>
								<td class="secondcolumnpay"></td>
								<td class="secondcolumnpay"></td>
							@endif
						</tr>
					<?php
						$cant2 = $i + $dias +1;
					}; ?>
				@endfor
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
