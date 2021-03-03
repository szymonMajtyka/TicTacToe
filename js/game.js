let ahTimer, playing = false;

function Play()
{
	if(!playing)
	{
		$(".card-body").show();
		$("#idFooter").show();
		ahTimer = setInterval(updateGameState, 1000);
		playing = true;
	}
	else
	{
		$(".card-body").hide();
		$("#idFooter").hide();
		clearInterval(ahTimer);
		playing = false;
	}
}

$(function(){
	$(".game-board td").click(function () {
		let anCellIndex_Click = $(this).data("cellindex");
		updateGameState(anCellIndex_Click);
	});

	$("#idModalWinner").on('hidden.bs.modal', function () {
		$(".card-body").hide();
		$("#idFooter").hide();
	});
})

function updateGameState(anCellIndex_Click) {
	let aRequest = {};
	if (undefined !== anCellIndex_Click) {
		aRequest["CellIndex_Click"] = anCellIndex_Click;
	}
	$.getJSON('php/getGameState.php', aRequest)
		.done(function (data, textStatus, jqXHR) {
			if (!data) {
				return;
			}
			let astrWeAreUserX = data["User"];
			let anNumUsers = data["NumUsers"];
			let astrBoard = data["Board"];
			let anRoundUser = data["RoundUser"];
			let astrWinner = data["Winner"];
			$("#idNumPlayers").text(anNumUsers);
			let abShowGamePanel = false;
			if (1 === anNumUsers) {
				$("#idFull").text("czekaj na swoją kolejkę");
				$("#idFull").show();
				$("#idUser, #idRoundUser, #idRoundEnemy").hide();
			}
			else {
				$("#idFull").text("");
				$("#idFull").hide();
				$("#idUser").text(astrWeAreUserX);
				$("#idUser").show();
				if (anNumUsers >= 2) {
					abShowGamePanel = true;
					switch (astrWeAreUserX) {
						case "UserA":
							$("#idRoundUser").text("UserA");
							$("#idRoundEnemy").text("UserB");
							break;
						case "UserB":
							$("#idRoundUser").text("UserB");
							$("#idRoundEnemy").text("UserA");
							break;
						default:
							break;
					}
				}
			}
			if (abShowGamePanel) {
				if (!$("#idGamePanel").hasClass("in")) {
					$("#idGamePanel").collapse("show");
				}
			} else {
				if ($("#idGamePanel").hasClass("in")) {
					$("#idGamePanel").collapse("hide");
				}
			}
			if (undefined !== anRoundUser) {
				anRoundUser = Number(anRoundUser);
				switch (anRoundUser) {
					case 0:
						switch (astrWeAreUserX) {
							case "UserA":
								$("#idRoundUser").show();
								$("#idRoundEnemy").hide();
								break;
							case "UserB":
								$("#idRoundUser").hide();
								$("#idRoundEnemy").show();
								break;
							default:
								break;
						}
						break;
					case 1:
						switch (astrWeAreUserX) {
							case "UserA":
								$("#idRoundUser").hide();
								$("#idRoundEnemy").show();
								break;
							case "UserB":
								$("#idRoundUser").show();
								$("#idRoundEnemy").hide();
								break;
							default:
								break;
						}
						break;
					default:
						$("#idRoundUser, #idRoundEnemy").hide();
						break;
				}
			} else {
				$("#idRoundUser, #idRoundEnemy").hide();
			}
			let anCountFields = 0;
			if (typeof astrBoard === "string") {
				let avCellsState = astrBoard.split(",");
				if (9 == avCellsState.length) {
					let i, n, anState, $aCell;
					for (i = 0, n = avCellsState.length; i < n; ++i) {
						anState = Number(avCellsState[i]);
						$aCell = $("#idCell" + i);
						switch (anState) {
							default:
							case 0:
								$aCell.removeClass("bg-primary bg-warning");
								$aCell.html("");
								break;
							case 1:
								++anCountFields;
								$aCell.html("O");
								switch (astrWeAreUserX) {
									case "UserA":
										$aCell.removeClass("bg-warning").addClass("bg-primary");
										break;
									case "UserB":
										$aCell.removeClass("bg-primary").addClass("bg-warning");
										break;
									default:
										break;
								}
								break;
							case 2:
								++anCountFields;
								$aCell.html("X");
								switch (astrWeAreUserX) {
									case "UserA":
										$aCell.removeClass("bg-primary").addClass("bg-warning");
										break;
									case "UserB":
										$aCell.removeClass("bg-warning").addClass("bg-primary");
										break;
									default:
										break;
								}
								break;
						}
					}
				}
			}
			if (("UserA" == astrWeAreUserX) || ("UserB" == astrWeAreUserX)) {
				if (typeof astrWinner === "string") {
					switch (astrWinner) {
						case "UserA":
						case "UserB":
							clearInterval(ahTimer);
							$("#idWinner").text(astrWinner);
							$("#idModalWinner").modal('show');
							break;
						default:
							break;
					}
				}
				if (9 <= anCountFields) {
					clearInterval(ahTimer);
					$("#idWinner").text("Remis");
					$("#idModalWinner").modal('show');
				}
			}
		}).fail(function (jqXHR, astrTextStatus, astrErrorThrown) {
			console.log("Error:" + astrTextStatus + '(' + astrErrorThrown + ')');
			console.warn(jqXHR.responseText);
		});
	
}

