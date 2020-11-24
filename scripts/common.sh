#!/bin/bash

cls()
{
 tput clear
 return 0
}

home()
{
 tput cup 0 0
 return 0
}

end()
{
 let x=$COLUMNS-1
 tput cup $LINES $x
}

bold()
{
 tput smso
}

unbold()
{
 tput rmso
}

underline()
{
 tput smul
}

normalline()
{
 tput rmul
}

color()
{
tput setaf $1
}

uncolor()
{
tput setaf 0
}

log()
{
bold
echo $1 
unbold
}

hint()
{
color 4
echo $1
uncolor
}

blue() {
echo "$(tput setaf 4)$1$(tput setaf 0)"
}

green() {
echo "$(tput setaf 2)$1$(tput setaf 0)"
}
