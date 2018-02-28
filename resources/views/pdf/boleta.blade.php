	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
	<style type="text/css">
		
		.body-width {
			width: 50%;
		}
		div.top-bar {
			height: 20px;
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
			height: 40px;
			font-size: 20px;
			color: black;
		}
		table{
			width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
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
			height: 20px;
			border: 1px solid black;
			font-size: 13px;
			text-align: center;	
		}

	</style>

	<body class="body-width">
		<div class="top-bar">
			<div class="blue-bar"></div>
		</div>
		<div class="title">BOLETA DE CONTROL DE PAGO</div>
		<table>
			<tr>
				<td>Nombre Cliente:</td>
				<td>
					<span><strong>{!! $data->cliente->nombre.' '.$data->cliente->apellido !!}</strong></span>
				</td>
				<td>Plan:</td>
				<td>
					<span><strong>{!! $data->planes->descripcion !!}</strong></span>
				</td>
			</tr>
			<tr>
				<td>Fecha de inicio:</td>
				<td>
					<span><strong>{!! $data->fecha_inicio!!}</strong></span>
				</td>
				<td>Fecha de finalización:</td>
				<td>
					<span><strong>{!! $data->fecha_fin!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td>Cuota diaria:</td>
				<td>
					<span><strong>{!! $data->cuota_diaria!!}</strong></span>
				</td>
				<td>Cobrador:</td>
				<td>
					<span><strong>{!! $data->usuariocobrador->nombre!!}</strong></span>
				</td>
			</tr>
		</table>
		<br>
		<?php 

			$totaldias = (strtotime($data->fecha_inicio)-strtotime($data->fecha_fin))/86400;
			$totaldias = abs($totaldias); 
			$totaldias = floor($totaldias);		

			$dias = intval(($totaldias / 2));
			$residuo = ($totaldias % 2);

		?>
		<table class="tablapago">
			<thead class="pago">
				<tr>
					<td class="columna">Fecha de Pago</td>
					<td class="columna">Firma</td>
					<td class="columna">Fecha de Pago</td>
					<td class="columna">Firma</td>
				</tr>
			</thead>
			<tbody>
				@for ($i = 0; $i < $dias; $i++)
					<?php
						$cant1 = $i+1;
						$cant2 = $i+1+$dias+($residuo);
						
						$fecha1 = strtotime ( '+'.$cant1.' day' , strtotime ( $data->fecha_inicio) ) ;
						$fecha1 = date ( 'j-m-Y' , $fecha1 );

						$fecha2 = strtotime ( '+'.$cant2.' day' , strtotime ( $data->fecha_inicio ) ) ;
						$fecha2 = date ( 'j-m-Y' , $fecha2 );
					?>
					<tr class="primeracolumna">
						<td class="columnapago">{!! $fecha1 !!}</td>
						<td class="columnapago"></td>
						<td class="columnapago">{!! $fecha2 !!}</td>
						<td class="columnapago"></td>
					</tr>
				@endfor
				@if ($residuo>0)
					<?php 
						$cant3 = $dias+($residuo);
					
						$fecha3 = strtotime ( '+'.$cant3.' day' , strtotime ( $data->fecha_inicio ) );
						$fecha3 = date ( 'j-m-Y' , $fecha3 );
					?>
					<tr class="primeracolumna">
						<td class="columnapago">{!! $fecha3 !!}</td>
						<td class="columnapago"></td>
					</tr>
				@endif
			</tbody>
		</table>
	</body>