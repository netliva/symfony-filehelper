var netliva_file_helper = {
	showPastBtnBind : function ()
	{
		$(".showPastBtn:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).click(function (e) {
				e.preventDefault();
				if ($(this).hasClass("openedList")) $(this).removeClass("openedList").closest(".fileInfo").next(
					".inPastForFile").slideUp(); else $(this).addClass("openedList").closest(".fileInfo").next(
					".inPastForFile").slideDown();
				return false;
			});
		});
	},

	showDeletedFileBind : function ()
	{
		$(".showDeletedFilesOfStack:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).click(function () {
				if ($(this).data("showed"))
				{
					$(this).parent().find(".file_not_exists").slideUp();
					$(this).html("Silinenleri Göster");
				}
				else
				{
					$(this).parent().find(".file_not_exists").slideDown();
					$(this).html("Silinenleri Gizle");
				}
				$(this).data("showed", !$(this).data("showed"));
			});
		});
	},

	showFileInfoByClickBind : function ()
	{
		$(".singleFileArea.singleFileArea-style-click .optClickBtn:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).next().show();
			});
		});
		if ($("html").data("showFileInfoByClickBind") !== "OK")
		{
			$("html")
				.data("showFileInfoByClickBind", "OK")
				.click(function () {
					$(".optClickArea").hide();
				});
		}
	},

	netlivaImageUploadBind : function ()
	{
		$(".netliva-image-upload-helper-form:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).ajaxForm({
				dataType: 'json',
				success: function (response) {
					if (response.status == "success") { }
					else
					{
						error(response.message);
					}
				}
			});
			$(this).change(function () {
				$(this).submit();
			});
		});
	},

	netlivaSingleUploadBind : function ()
	{
		$(".netlivaSingleUpload:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).ajaxForm({
				 dataType: 'json',
				 success: function (response, status, xhr, $form)
				 {
					if (response.status === "success" || response.status === "partial_success")
					{
						$form.find('.fileinput .btn')
							.removeClass("btn-success")
							.addClass("btn-info");
						if (response.total > 1 && response.status === "success") success(
							"Tüm dosyalar başarıyla yüklendi",
							600
						); else
							if (response.status === "partial_success") warning(response.total + " adet dosyadan " + response.success + " adedi başarıyla yüklendi. <br>Yüklenemeyenlerin sebebi; <br> - " + response.messages.join(
								'<br> - '));
					}
					else
					{
						error("Yüklemek istediğiniz " + (response.total > 1 ? " hiç bir " : "") + " dosya yüklenemedi. <br>Sebepler; <br> - " + response.messages.join(
							'<br> - '));
					}
					$form.find('input[name="singleFile[]"]').val();
					$form.find('input[name="singleFile"]').val();
				}, error: function (response, status, xhr, $form) {
					$form.find('input[name="singleFile[]"]').val();
					$form.find('input[name="singleFile"]').val();
				}
			 });
			$(this).find('input[name="singleFile[]"]').change(function () {
				$(this).closest("form").submit();
			});
			$(this).find('input[name=singleFile]').change(function () {
				$form = $(this).closest("form");
				options = JSON.parse($form.find('input[name=opt]').val());
				if ($form.find('input[name=listId]').val()) $form.submit(); else
					if ($form.data("getName") || $form.data("selectDate") || $form.data("hardStackList"))
					{
						var content = '';
						if ($form.data("hardStackList"))
						{
							content += '<h4><br><br>Yüklenen Dosya : </h4><select type="text" name="stack_temp" id="stack_temp" class="form-control input" style="margin-top:10px">';
							$.each($form.data("hardStackList"), function (stcKey, stack) {
								content += '<option value="' + stcKey + '">' + stack.name + '</option>';
							});
							content += '</select>';
						}
						else
							if ($form.data("getName")) content += '<h4><br><br>Dosya Adı: </h4><input type="text" name="name_temp" id="name_temp" value="' + $form.find(
								"input[name='name']").val() + '" class="form-control input" style="margin-top:10px" />';

						if ($form.data("selectDate")) content += '<h4><br>Tarih: </h4><input type="text" id="file_date_helper" value="" class="form-control input" style="margin-top:10px" />';


						myDialogBox({
							id: 'SingleFileApprow',
							title: 'Dosya Bilgileri',
							content: content,
							init: function () {
								if ($form.data("selectDate"))
								{
									var opt = {
										locale: 'tr', format: 'DD MMMM YYYY, dddd - HH:mm',
									};
									if (typeof options.maxDate !== "undefined") opt.maxDate = options.maxDate;
									if (typeof options.minDate !== "undefined") opt.minDate = options.minDate;
									$("#generalDialogBoxSingleFileApprow #file_date_helper")
										.datetimepicker(opt)
										.on("dp.change", function (e) {
											$form.find('input[name=file_date]').val(moment(e.date).format(
												"YYYY-MM-DD HH:mm"));
										});
								}

							},
							buttons: [
								{
									label: 'Gönder', class: 'success', action: function (e) {
										if (!$form.data("selectDate") || $form.find('input[name=file_date]').val())
										{
											$("#generalDialogBoxSingleFileApprow").modal('hide');
											if ($form.data("hardStackList")) $form.find("input[name='name']").val(
												$("#generalDialogBoxSingleFileApprow #stack_temp").val()); else $form.find(
												"input[name='name']").val($(
												"#generalDialogBoxSingleFileApprow #name_temp").val());
											$form.submit();
										}
										else
											if ($form.data("selectDate"))
											{
												alert("Tarih seçimi yapınız");
												$("#generalDialogBoxSingleFileApprow #file_date_helper").focus();
											}
									}
								}, {label: 'Vazgeç', action: 'close', class: 'danger'}
							],
						});
					}
					else
					{
						$form.submit();
					}
			})
		});
	},


	refreshFileAreas : function (response)
	{
		if ($("#netliva-file-list-" + response.fileGroup).length && $("#opt" + response.fileGroup).length) netliva_file_helper.refreshFileSoftLine(
			response.fileGroup); else
			if ($("#singleFileArea-" + response.fileGroup + "-" + response.fileCode).length && $("#opt" + response.fileGroup + "-" + response.fileCode).length) netliva_file_helper.refreshSingleFileUpload(
				response.fileGroup,
				response.fileCode
			); else netliva_file_helper.refreshFileHardLine(response.fileGroup, response.fileCode);
		$('#generalDialogBox').modal('hide');
	},

	netlivaFileControlBind : function ()
	{
		$(".file_control_buttons:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).find(".btn-success").click(function () {
				file_name = $(this).closest(".file_control_buttons").data("fileName");
				file_id = $(this).closest(".file_control_buttons").data("fileId");
				myDialogBox({
					title: 'Dosya Onayı',
					content: '<strong>`' + file_name + '`</strong> isimli dosyanın kontrolünü yapıp UYGUN olduğunu onaylıyor musunuz?',
					buttons: [
						{
							label: 'Dosyayı Onayla', class: 'success', action: function (e) {
								$.ajax({
										url: netliva_file_assess_path, data: {
										"operation": "approve", "fileId": file_id,
									}, dataType: "json", type: "post", success: netliva_file_helper.refreshFileAreas
									});
							}
						}, {label: 'Vazgeç', action: 'close', class: 'danger'}
					],
				});
			});
			$(this).find(".btn-danger").click(function () {
				file_name = $(this).closest(".file_control_buttons").data("fileName");
				file_id = $(this).closest(".file_control_buttons").data("fileId");
				myDialogBox({
					title: 'Uygunsuzluk Belirtme',
					content: '<div class="text-center mrg15B"><strong>`' + file_name + '`</strong> isimli dosya hakkında <br />UYGUNSUZ\'luk açıklamalarınız belirtiniz;</div> <div class="form-group"> <label class="col-sm-4 control-label required" for="file_suit_desc">Açıklamalar:</label><div class="col-sm-8"><textarea id="file_suit_desc" required="required" class="form-control"></textarea></div>',
					buttons: [
						{
							label: 'Uygunsuzluğu Gönder', class: 'success', action: function (e) {
								$.ajax({
										url: netliva_file_assess_path, data: {
										"operation": "rejection",
										"fileId": file_id,
										"description": $("#file_suit_desc").val(),
									}, dataType: "json", type: "post", success: netliva_file_helper.refreshFileAreas
									});
							}
						}, {label: 'Vazgeç', action: 'close', class: 'danger'}
					],
				});
			});
		});

		$(".all_approve_button:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).find(".btn-success").click(function () {
				group_name = $(this).closest(".all_approve_button").data("groupName");
				file_group = $(this).closest(".all_approve_button").data("fileGroup");
				myDialogBox({
					title: 'Dosya Onayı',
					content: '<strong>`' + group_name + '`</strong> adlı gruptaki <u>onay bekleyen</u> dosyaların tümünün kontrolünü yapıp hepsinin UYGUN olduğunu onaylıyor musunuz?',
					buttons: [
						{
							label: 'Dosyayı Onayla', class: 'success', action: function (e) {
								$.ajax({
										url: netliva_all_approve_path, data: {
										"operation": "approve", "fileGroup": file_group,
									}, dataType: "json", type: "post", success: netliva_file_helper.refreshFileAreas
									});
							}
						}, {label: 'Vazgeç', action: 'close', class: 'danger'}
					],
				});
			});
		});
	},

	netlivaFilePrepareInformationBind : function ()
	{
		$(".file_list_prepare_information:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).find(".btn-success").click(function () {
				file_name = $(this).closest(".file_list_prepare_information").data("name");
				url = $(this).closest(".file_list_prepare_information").data("url");
				if (url)
				{
					myDialogBox({
						id: 'PrepareInfo',
						title: 'Dosya Bilgileri',
						url: url,
						data: {file_name: file_name},
						buttons: [
							{
								label: 'Bilgiyi Kaydet', class: 'success', action: function (e) {
									$.ajax({
										url: url,
										dataType: "json",
										type: "post",
										data: {"description": $("#file_info").val(),},
										success: function () {
											$('#generalDialogBoxPrepareInfo').modal('hide');
											window.location.reload();
										}
									});
								}
							}, {label: 'Vazgeç', action: 'close', class: 'danger'}
						],
					});
				}
			});
		});
	},

	netlivaDeleteFileBind : function ()
	{
		$(".netlivaDeleteFile:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).click(function (e) {
				e.preventDefault();
				var deleteUrl = $(this).data("deleteUrl");
				var refresh = $(this).data("refresh");
				myDialogBox({
					title: 'Silme Onayı',
					content: 'İlgili dosyayı silmek istediğinizden emin misiniz?',
					buttons: [
						{
							label: 'SİL', class: 'danger', action: function (e) {
								$('#generalDialogBox').modal('hide');
								$.ajax({
									url: deleteUrl,
									data: {},
									dataType: "json",
									type: "post",
									success: function () {
										eval(refresh);
									}
								});
							}
						}, {label: 'Vazgeç', action: 'close', class: 'warning'}
					],
				});
				return false;
			});
		});
	},

	fileListSubMenuToogleBind : function ()
	{
		$(".list-sub-group:not(.binded)").each(function () {
			$(this).addClass("binded");
			$(this).find(".sub_list_title").click(function () {
				if ($(this).closest(".list-sub-group").hasClass("open")) $(this).closest(".list-sub-group").removeClass(
					"open"); else $(this).closest(".list-sub-group").addClass("open");

				window.localStorage.setItem(
					"sub_list_open_" + $(this).closest(".list-group-item").attr("id"),
					$(this).closest(".list-sub-group").hasClass("open")
				);
			});

			is_open = window.localStorage.getItem("sub_list_open_" + $(this).closest(".list-group-item").attr("id")) === "true";
			if (is_open) $(this).addClass("open");
		});
	},

	refreshFileHardLine : function (fileGroup, fileCode)
	{
		$id = "#" + fileGroup + "-" + fileCode;
		if (!$($id).length) $id = "#" + fileGroup;

		if ($($id).length)
		{
			$.ajax({
				url: $($id).data("refreshUrl"),
				dataType: "html",
				type: "post",
				data: {
					fileGroup: fileGroup,
					listId: $($id).parent().data("listId"),
					options: $($id).parent().data("options"),
					fileCode: fileCode
				}, success: function (response) {
					$($id).html(response);
				}
			});
		}
	},

	refreshFileSoftLine : function (fileGroup)
	{
		$.ajax({
			url: $("#netliva-file-list-" + fileGroup).data("refreshUrl"),
			dataType: "html",
			type: "post",
			data: {opt: $("#opt" + fileGroup).val()},
			success: function (response) {
				$("#netliva-file-list-" + fileGroup + " .list-group").html(response);
			}
		});


	},

	refreshSingleFileUpload : function (fileGroup, fileCode)
	{
		$.ajax({
			url: $("#singleFileArea-" + fileGroup + "-" + fileCode).data("refreshUrl"),
			dataType: "html",
			type: "post",
			data: {
				fileGroup: fileGroup, fileCode: fileCode, opt: $("#opt" + fileGroup + "-" + fileCode).val(),
			},
			success: function (response) {
				$("#singleFileArea-" + fileGroup + "-" + fileCode).html(response);
			}
		});

	},

	resizeNetlivaFileList : function ()
	{
		$(".netliva-file-list").each(function () {
			if ($(this).width() < 600) $(this).find(".fileinput .btn > span > span").hide(); else $(this).find(
				".fileinput .btn > span > span").show();
		});
	},


	init : function ()
	{
		this.showPastBtnBind();
		this.netlivaDeleteFileBind();
		this.fileListSubMenuToogleBind();
		this.showFileInfoByClickBind();
		this.netlivaSingleUploadBind();
		this.netlivaImageUploadBind();
		this.netlivaFileControlBind();
		this.showDeletedFileBind();
		this.netlivaFilePrepareInformationBind();
	},
}
jQuery(function ($) {
	$(window).resize(netliva_file_helper.resizeNetlivaFileList);
	netliva_file_helper.resizeNetlivaFileList();
	netliva_file_helper.init();
});

$(document).bind("ajaxComplete", function(event, jqXHR, ajaxOptions){
	setTimeout(function () {
		netliva_file_helper.resizeNetlivaFileList();
		netliva_file_helper.init();
	},100);

	if (typeof netliva_file_singleFileUpload_path != "undefined" && typeof jqXHR.responseJSON !== "undefined")
	{
		path = netliva_file_singleFileUpload_path.split("/");
		test = ajaxOptions.url.split("/");
		urlControl = test[3] === path[1] && test[4] === path[2] && test[5] === path[3];

		if (urlControl)
		{
			netliva_file_helper.refreshFileAreas(jqXHR.responseJSON)
		}
	}

});

