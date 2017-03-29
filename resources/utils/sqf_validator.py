#!/usr/bin/env python3

import fnmatch
import os
import re
import ntpath
import sys
import argparse

def validKeyWordAfterCode(content, index):
    keyWords = ["for", "do", "count", "each", "forEach", "else", "and", "not", "isEqualTo", "in", "call", "spawn", "execVM", "catch", "param", "select", "apply"];
    for word in keyWords:
        try:
            subWord = content.index(word, index, index+len(word))
            return True;
        except:
            pass
    return False

def check_sqf_syntax(filepath):
    bad_count_file = 0
    def pushClosing(t):
        closingStack.append(closing.expr)
        closing << Literal( closingFor[t[0]] )

    def popClosing():
        closing << closingStack.pop()

    with open(filepath, 'r') as file:
        content = file.read()
        relfilename = os.path.basename(filepath)

        # Store all brackets we find in this file, so we can validate everything on the end
        brackets_list = []

        # To check if we are in a comment block
        isInCommentBlock = False
        checkIfInComment = False
        # Used in case we are in a line comment (//)
        ignoreTillEndOfLine = False
        # Used in case we are in a comment block (/* */). This is true if we detect a * inside a comment block.
        # If the next character is a /, it means we end our comment block.
        checkIfNextIsClosingBlock = False

        # We ignore everything inside a string
        isInString = False
        # Used to store the starting type of a string, so we can match that to the end of a string
        inStringType = '';

        lastIsCurlyBrace = False
        checkForSemiColumn = False

        # Extra information so we know what line we find errors at
        lineNumber = 1

        indexOfCharacter = 0
        # Parse all characters in the content of this file to search for potential errors
        for c in content:
            if (lastIsCurlyBrace):
                lastIsCurlyBrace = False
                checkForSemiColumn = True

            if c == '\n': # Keeping track of our line numbers
                lineNumber += 1 # so we can print accurate line number information when we detect a possible error
            if (isInString): # while we are in a string, we can ignore everything else, except the end of the string
                if (c == inStringType):
                    isInString = False
            # if we are not in a comment block, we will check if we are at the start of one or count the () {} and []
            elif (isInCommentBlock == False):

                # This means we have encountered a /, so we are now checking if this is an inline comment or a comment block
                if (checkIfInComment):
                    checkIfInComment = False
                    if c == '*': # if the next character after / is a *, we are at the start of a comment block
                        isInCommentBlock = True
                    elif (c == '/'): # Otherwise, will check if we are in an line comment
                        ignoreTillEndOfLine = True # and an line comment is a / followed by another / (//) We won't care about anything that comes after it

                if (isInCommentBlock == False):
                    if (ignoreTillEndOfLine): # we are in a line comment, just continue going through the characters until we find an end of line
                        if (c == '\n'):
                            ignoreTillEndOfLine = False
                    else: # validate brackets
                        if (c == '"' or c == "'"):
                            isInString = True
                            inStringType = c
                        elif (c == '#'):
                            ignoreTillEndOfLine = True
                        elif (c == '/'):
                            checkIfInComment = True
                        elif (c == '('):
                            brackets_list.append('(')
                        elif (c == ')'):
                            if (brackets_list[-1] in ['{', '[']):
                                print("Possible missing round bracket ')' detected at line {1}\\n".format(relfilename,lineNumber))
                                bad_count_file += 1
                            brackets_list.append(')')
                        elif (c == '['):
                            brackets_list.append('[')
                        elif (c == ']'):
                            if (brackets_list[-1] in ['{', '(']):
                                print("Possible missing square bracket ']' detected at line {1}\\n".format(relfilename,lineNumber))
                                bad_count_file += 1
                            brackets_list.append(']')
                        elif (c == '{'):
                            brackets_list.append('{')
                        elif (c == '}'):
                            lastIsCurlyBrace = True
                            if (brackets_list[-1] in ['(', '[']):
                                print("Possible missing curly brace '}}' detected at line {1}\\n".format(relfilename,lineNumber))
                                bad_count_file += 1
                            brackets_list.append('}')

                        if (checkForSemiColumn):
                            if (c not in [' ', '\t', '\n', '/']): # keep reading until no white space or comments
                                checkForSemiColumn = False
                                if (c not in [']', ')', '}', ';', ',', '&', '!', '|', '='] and not validKeyWordAfterCode(content, indexOfCharacter)): # , 'f', 'd', 'c', 'e', 'a', 'n', 'i']):
                                    print("Possible missing semi-column ';' detected at line {1}\\n".format(relfilename,lineNumber))
                                    bad_count_file += 1

            else: # Look for the end of our comment block
                if (c == '*'):
                    checkIfNextIsClosingBlock = True;
                elif (checkIfNextIsClosingBlock):
                    if (c == '/'):
                        isInCommentBlock = False
                    elif (c != '*'):
                        checkIfNextIsClosingBlock = False
            indexOfCharacter += 1

        if brackets_list.count('[') != brackets_list.count(']'):
            print("A possible missing square bracket [ or ]\\n".format(relfilename,brackets_list.count('['),brackets_list.count(']')))
            bad_count_file += 1
        if brackets_list.count('(') != brackets_list.count(')'):
            print("A possible missing round bracket ( or )\\n".format(relfilename,brackets_list.count('('),brackets_list.count(')')))
            bad_count_file += 1
        if brackets_list.count('{') != brackets_list.count('}'):
            print("A possible missing curly brace {{ or }}\\n".format(relfilename,brackets_list.count('{'),brackets_list.count('}')))
            bad_count_file += 1
    return bad_count_file

def main():
    args = sys.argv
    bad_count = check_sqf_syntax(args[1])
    return bad_count

if __name__ == "__main__":
    sys.exit(main())
