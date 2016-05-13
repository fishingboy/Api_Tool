chcp 950
@ECHO OFF

rem =======  �O����e�ؿ�  =======================
set debug=0
set curr_path=%CD%

rem =======  ��M�O�_�� git  =======================
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

rem =======  �ɮ׽ƻs  ==========================
if %git_find% == 1 (
    %git_sh% --login -i %curr_path%\installer\git_clone.sh
) else (
    if not exist D:\www\www_api_tool (
        robocopy %curr_path% D:\www\www_api_tool /E
        echo =======  [1] �ɮ׽ƻs����  =============================
        if %debug% == 1 (
            @call %curr_path%\installer\confirm.cmd
        ) else (
            @call %curr_path%\installer\sleep.cmd
        )
    )
)

rem =======  �ƻs list_model.php  ==========================
copy /y  D:\www\www_api_tool\application\models\list_model.sample.php D:\www\www_api_tool\application\models\list_model.php

rem =======  stop apache and mysql  ==============
C:\xampp\xampp_stop.exe
echo =======  [2] apache �� mysql ����.  =============================
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  �ק� apache �]�w  ==========================
C:\xampp\php\php %curr_path%\installer\text_editor.php
copy /y %curr_path%\installer\httpd-vhosts-api-tool.conf C:\xampp\apache\conf\extra
echo =======  [3] �ק� apache �]�w����  =============================
if %debug% == 1 (
    @call %curr_path%\installer\confirm.cmd
) else (
    @call %curr_path%\installer\sleep.cmd
)

rem =======  stop apache and mysql  ==============
C:\xampp\xampp_start.exe
echo =======  [4] Apache �� MySQL ���ҧ���  =============================
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

rem =======  �}�Һ��� ==================
"C:\Program Files (x86)\Mozilla Firefox\firefox.exe" http://localhost:777