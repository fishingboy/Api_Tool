SET /p confirm=請確認是否繼續安裝 (Y/N)? 

set flag=0
if %confirm% == y set flag=1
if %confirm% == Y set flag=1
if %flag% == 0 (
    echo 安裝已被取消!
    pause
    exit;
) else (
    @call %curr_path%\installer\sleep.cmd
)
