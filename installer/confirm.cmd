SET /p confirm=�нT�{�O�_�~��w�� (Y/N)? 

set flag=0
if %confirm% == y set flag=1
if %confirm% == Y set flag=1
if %flag% == 0 (
    echo �w�ˤw�Q����!
    pause
    exit;
) else (
    @call %curr_path%\installer\sleep.cmd
)
