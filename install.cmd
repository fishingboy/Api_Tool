chcp 950
@ECHO OFF

rem =======  記錄當前目錄  =======================
set debug=0
set curr_path=%CD%

rem =======  找尋是否有 git  =======================
set git_find=0
if exist %appdata%\..\Local\Programs\Git\bin\sh.exe (
    set git_sh=%appdata%\..\Local\Programs\Git\bin\sh.exe
    set git_find=1
)
if exist "C:\Program Files (x86)\Git\bin\sh.exe" (
    set git_sh="C:\Program Files (x86)\Git\bin\sh.exe"
    set git_find=1
)
if exist "D:\Git\bin\sh.exe" (
    set git_sh="D:\Git\bin\sh.exe"
    set git_find=1
)

rem =======  檔案複製  ==========================
if %git_find% == 1 (
    %git_sh% --login -i %curr_path%\installer\git_clone.sh
) else (
    if not exist D:\www\www_api_tool (
        robocopy %curr_path% D:\www\www_api_tool /E
        echo =======  [1] 檔案複製完成  =============================
        if %debug% == 1 (
            @call %curr_path%\installer\confirm.cmd
        ) else (
            @call %curr_path%\installer\sleep.cmd
        )
    )
)

rem =======  複製 list_model.php  ==========================
copy /y  D:\www\www_api_tool\application\models\list_model.sample.php D:\www\www_api_tool\application\models\list_model.php

rem =======  stop apache and mysql  ==============
C:\xampp\xampp_stop.exe
echo =======  [2] apache 及 mysql 停止.  =============================
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  修改 apache 設定  ==========================
C:\xampp\php\php %curr_path%\installer\text_editor.php
copy /y %curr_path%\installer\httpd-vhosts-api-tool.conf C:\xampp\apache\conf\extra
echo =======  [3] 修改 apache 設定完成  =============================
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  stop apache and mysql  ==============
C:\xampp\xampp_start.exe
echo =======  [4] Apache 及 MySQL 重啟完成  =============================
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  Success Message  ==================
type  %curr_path%\installer\success.txt
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  開啟網頁 ==================
"C:\Program Files (x86)\Mozilla Firefox\firefox.exe" http://localhost:777