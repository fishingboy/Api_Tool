echo ""
echo "<<<  Remove git folder: D:\www\www_api_tool  >>>"
echo ""
rm -rf /d/www/www_api_tool

echo ""
echo "<<<  Git clone  >>>"
echo ""
git clone git@gitlab.uitox-inside.com:uitox-markplace-tw/api_tool.git /d/www/www_api_tool

exit